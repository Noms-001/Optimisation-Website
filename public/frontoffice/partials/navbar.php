<div class="top-bar">
    <div class="date"><?php echo '' ?></div>
    <div class="urgent-ticker">DERNIÈRE MINUTE</div>
</div>
<div class="main-header">
    <div class="logo">
        <h2>Horizon<span>Info</span></h2>
    </div>
    <nav>
        <ul>
            <a href="/tous-nos-articles">
                <li <?php echo $categorie == null ? "class='active'" : "" ?>>Tous</li>
            </a>
            <?php foreach ($types as $type) { ?>
                <a href="/tous-nos-articles?categorie=<?=  $type-> libelle ?>">
                    <li <?php echo $categorie == $type->libelle ? "class='active'" : "" ?>><?php echo $type->libelle ?></li>
                </a>
            <?php } ?>
        </ul>
    </nav>
</div>