<footer>
    <div class="footer-content">
        <div>
            <h4 style="color:white;">HorizonInfo</h4>
            <p style="margin-top: 12px;">L'information rigoureuse au service de la compréhension internationale.</p>
        </div>
        <div>
            <h4 style="color:white;">Sections</h4>
            <ul style="list-style: none; margin-top:12px;">
                <?php foreach ($types as $type) { ?>
                    <li><a href="/tous-nos-articles?categorie=<?php echo $type->libelle ?>"><?php echo $type->libelle ?></a></li>
                <?php } ?>
            </ul>
        </div>
        <div>
            <h4 style="color:white;">Suivez-nous</h4>
            <p>Twitter / LinkedIn / RSS</p>
        </div>
        <div>
            <h4 style="color:white;">Mentions</h4>
            <p>Sources vérifiées & Éthique</p>
        </div>
    </div>
    <div class="footer-copyright">
        © 2026 HorizonInfo — Conçu pour une information indépendante et responsable.
    </div>
</footer>