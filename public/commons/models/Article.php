<?php

require_once __DIR__ . '/../utils/Util.php';

class Article
{
    public $id;
    public $titre;
    public $metaDescription;
    public $contenu;
    public $motClePrincipal;
    public $motCleSecondaire;
    public $priorite;
    public $imageSrc;
    public $imageAlt;
    public $datePublication;
    public $auteur; // Auteur object ou id
    public $type;   // TypeArticle object ou id
    public $media = []; // Liste Media

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getUrl()
    {
        $slug = Util::slugify($this->titre);

        return "/article/" . $this->id . "-" . $slug;
    }

    public function getRecommandationTitre(): array
    {
        $score = 0;
        $message = "";

        $titre = trim($this->titre ?? "");

        if (empty($titre)) {
            return [
                "score" => 0,
                "message" => "Titre manquant (critique pour le SEO)"
            ];
        }

        $longueur = strlen($titre);

        if ($longueur < 30) {
            $score = 1;
            $message = "Titre trop court (moins de 30 caractères)";
        } elseif ($longueur >= 30 && $longueur <= 60) {
            $score = 5;
            $message = "Titre optimisé pour le SEO";
        } else {
            $score = 3;
            $message = "Titre trop long (risque de coupure dans Google)";
        }

        return [
            "score" => $score,
            "message" => $message
        ];
    }

    public function getRecommandationMetaDescription(): array
    {
        $meta = trim($this->metaDescription ?? "");
        $longueur = strlen($meta);

        if (empty($meta)) {
            return [
                "score" => 0,
                "message" => "Meta description manquante (très important pour le SEO)"
            ];
        }

        if ($longueur < 80) {
            return [
                "score" => 1,
                "message" => "Meta description trop courte (moins de 80 caractères)"
            ];
        }

        if ($longueur > 160) {
            return [
                "score" => 2,
                "message" => "Meta description trop longue (risque de coupure dans Google)"
            ];
        }

        return [
            "score" => 4,
            "message" => "Meta description optimisée pour le SEO"
        ];
    }

    public function getRecommandationMotClePrincipal(): array
    {
        $titre = strtolower(trim($this->titre ?? ""));
        $motCle = strtolower(trim($this->motClePrincipal ?? ""));

        if (empty($motCle)) {
            return [
                "score" => 0,
                "message" => "Aucun mot-clé principal défini"
            ];
        }

        $mots = array_filter(array_map('trim', explode(",", $motCle)));

        if (empty($mots)) {
            return [
                "score" => 0,
                "message" => "Mot-clé principal invalide"
            ];
        }

        $total = count($mots);
        $found = 0;

        foreach ($mots as $mot) {
            if (strpos($titre, $mot) !== false) {
                $found++;
            }
        }

        if ($found === 0) {
            $score = 1;
            $message = "Aucun mot-clé principal présent dans le titre";
        } elseif ($found < $total) {
            $score = 3;
            $message = "$found/$total mots-clés principaux présents";
        } else {
            $score = 5;
            $message = "Tous les mots-clés principaux sont présents dans le titre";
        }

        return [
            "score" => $score,
            "message" => $message
        ];
    }

    public function getRecommandationSousTitres(): array
    {
        $contenu = $this->contenu ?? "";
        $motCle = strtolower(trim($this->motCleSecondaire ?? ""));

        if (empty($motCle)) {
            return [
                "score" => 0,
                "message" => "Aucun mot-clé secondaire défini"
            ];
        }

        $mots = array_filter(array_map('trim', explode(",", $motCle)));

        if (empty($mots)) {
            return [
                "score" => 0,
                "message" => "Mots-clés secondaires invalides"
            ];
        }

        // Extraire H2-H6
        preg_match_all('/<h[2-6][^>]*>(.*?)<\/h[2-6]>/', strtolower($contenu), $matches);

        $headings = $matches[1] ?? [];
        $headingsText = implode(" ", $headings);

        $total = count($mots);
        $found = 0;

        foreach ($mots as $mot) {
            if (!empty($mot) && strpos($headingsText, $mot) !== false) {
                $found++;
            }
        }

        // ========================
        // SCORE
        // ========================
        if ($found === 0) {
            $score = 1;
            $message = "Aucun mot-clé secondaire présent dans les sous-titres (H2-H6)";
        } elseif ($found < $total) {
            $score = 2;
            $message = "$found/$total mots-clés secondaires trouvés dans H2-H6";
        } else {
            $score = 4;
            $message = "Tous les mots-clés secondaires sont présents dans les sous-titres";
        }

        return [
            "score" => $score,
            "message" => $message
        ];
    }

    public function getRecommandationImages(): array
    {
        $motCle = strtolower(trim($this->motCle ?? ""));
        $motPrincipal = "";

        if (!empty($motCle)) {
            $mots = explode(",", $motCle);
            $motPrincipal = strtolower(trim($mots[0]));
        }

        $coverOk = false;

        if (!empty($this->imageSrc)) {
            $alt = strtolower(trim($this->imageAlt ?? ""));

            if (!empty($alt) && strlen($alt) >= 5) {

                if (empty($motPrincipal) || substr_count($alt, $motPrincipal) < 3) {
                    $coverOk = true;
                }
            }
        }

        $totalMedia = count($this->media);
        $validMedia = 0;

        foreach ($this->media as $m) {
            $alt = strtolower(trim($m->alt ?? ""));

            if (empty($alt)) continue;
            if (strlen($alt) < 5) continue;

            if (!empty($motPrincipal) && substr_count($alt, $motPrincipal) >= 3) continue;

            $validMedia++;
        }

        if (empty($this->imageSrc) && $totalMedia === 0) {
            return [
                "score" => 0,
                "message" => "Aucune image (ni couverture ni galerie)"
            ];
        }

        $ratio = $totalMedia > 0 ? ($validMedia / $totalMedia) : 0;

        if (!$coverOk && $ratio < 0.5) {
            $score = 1;
            $message = "Images mal optimisées (ALT manquant ou mauvaise qualité)";
        } elseif ($coverOk || $ratio >= 0.5) {
            $score = 2;
            $message = "Images partiellement optimisées";
        }
        if ($coverOk && ($totalMedia === 0 || $ratio === 1)) {
            $score = 3;
            $message = "Images parfaitement optimisées (couverture + ALT)";
        }

        return [
            "score" => $score,
            "message" => $message
        ];
    }

    public function getRecommandationDensiteMotCle(): int
    {
        if (empty($this->contenu) || empty($this->motClePrincipal)) {
            return 0;
        }

        $keywords = array_filter(array_map('trim', explode(',', $this->motClePrincipal)));

        if (empty($keywords)) {
            return 0;
        }

        $text = strtolower(strip_tags($this->contenu));

        $words = str_word_count($text);
        if ($words === 0) {
            return 0;
        }

        $totalDensityScore = 0;
        $validKeywords = 0;

        foreach ($keywords as $keyword) {
            $keyword = strtolower($keyword);

            if (empty($keyword)) {
                continue;
            }

            // Compter occurrences exactes
            $occurrences = preg_match_all('/\b' . preg_quote($keyword, '/') . '\b/u', $text);

            $density = ($occurrences / $words) * 100;

            // Score par mot-clé
            if ($density >= 1 && $density <= 2) {
                $totalDensityScore += 4;
            } elseif (($density >= 0.5 && $density < 1) || ($density > 2 && $density <= 3)) {
                $totalDensityScore += 3;
            } elseif ($density >= 0.2 && $density < 0.5) {
                $totalDensityScore += 2;
            } elseif ($density > 0) {
                $totalDensityScore += 1;
            } else {
                $totalDensityScore += 0;
            }

            $validKeywords++;
        }

        if ($validKeywords === 0) {
            return 0;
        }

        // Moyenne des scores
        return (int) round($totalDensityScore / $validKeywords);
    }

    public function getScoreSEOGlobal(): array
    {
        $messages = [];
        $scoreTotal = 0;
        $scoreMax = 25;

        $titre = $this->getRecommandationTitre();
        $scoreTotal += $titre["score"];
        $messages[] = $titre["message"];

        $meta = $this->getRecommandationMetaDescription();
        $scoreTotal += $meta["score"];
        $messages[] = $meta["message"];

        $motCle = $this->getRecommandationMotClePrincipal();
        $scoreTotal += $motCle["score"];
        $messages[] = $motCle["message"];

        $hn = $this->getRecommandationSousTitres();
        $scoreTotal += $hn["score"];
        $messages[] = $hn["message"];

        $images = $this->getRecommandationImages();
        $scoreTotal += $images["score"];
        $messages[] = $images["message"];

        $densiteScore = $this->getRecommandationDensiteMotCle();
        $scoreTotal += $densiteScore;

        if ($densiteScore === 4) {
            $messages[] = "Densité du mot-clé optimale (1% à 2%)";
        } elseif ($densiteScore === 3) {
            $messages[] = "Densité correcte mais améliorable";
        } elseif ($densiteScore === 2) {
            $messages[] = "Mot-clé peu présent dans le contenu";
        } elseif ($densiteScore === 1) {
            $messages[] = "Mauvaise densité (trop faible ou trop élevée)";
        } else {
            $messages[] = "Mot-clé principal absent du contenu";
        }

        $scoreFinal = ($scoreTotal / $scoreMax) * 100;

        return [
            "score" => round($scoreFinal),
            "messages" => $messages
        ];
    }

    public function extract(): array
    {
        $html = $this->contenu ?? "";

        if (empty($html)) {
            return [
                "text" => "",
                "headings" => [],
                "links" => []
            ];
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $text = trim($dom->textContent);

        $headings = [];

        for ($i = 2; $i <= 6; $i++) {
            $tags = $dom->getElementsByTagName("h$i");

            foreach ($tags as $tag) {
                $headings[] = [
                    "level" => "h$i",
                    "text" => trim($tag->textContent)
                ];
            }
        }

        $links = [];

        $aTags = $dom->getElementsByTagName("a");

        foreach ($aTags as $a) {
            $href = $a->getAttribute("href");

            $links[] = [
                "href" => $href,
                "text" => trim($a->textContent)
            ];
        }

        return [
            "text" => trim($text),
            "headings" => $headings,
            "links" => $links
        ];
    }
}
