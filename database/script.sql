CREATE TABLE auteur(
   id_auteur INT AUTO_INCREMENT,
   nom VARCHAR(250)  NOT NULL,
   email VARCHAR(250) ,
   role VARCHAR(150) ,
   PRIMARY KEY(id_auteur)
);

CREATE TABLE type_article(
   id_type INT AUTO_INCREMENT,
   libelle VARCHAR(150)  NOT NULL,
   PRIMARY KEY(id_type)
);

CREATE TABLE utilisateur(
   id_utilisateur INT AUTO_INCREMENT,
   email VARCHAR(250) ,
   mot_de_passe VARCHAR(50)  NOT NULL,
   PRIMARY KEY(id_utilisateur)
);

CREATE TABLE article(
   id_article INT AUTO_INCREMENT,
   titre VARCHAR(250)  NOT NULL,
   meta_description VARCHAR(255)  NOT NULL,
   contenu TEXT NOT NULL,
   mot_cle_principal VARCHAR(255)  NOT NULL,
   mot_cle_secondaire VARCHAR(255)  NOT NULL,
   priorite BOOLEAN,
   nombre_vue INT,
   img_src VARCHAR(255)  NOT NULL,
   img_alt VARCHAR(250)  NOT NULL,
   date_publication DATETIME NOT NULL,
   id_auteur INT NOT NULL,
   id_type INT NOT NULL,
   PRIMARY KEY(id_article),
   FOREIGN KEY(id_auteur) REFERENCES auteur(id_auteur),
   FOREIGN KEY(id_type) REFERENCES type_article(id_type)
);

CREATE TABLE media(
   id_media INT AUTO_INCREMENT,
   src VARCHAR(250)  NOT NULL,
   alt VARCHAR(250)  NOT NULL,
   id_article INT NOT NULL,
   PRIMARY KEY(id_media),
   FOREIGN KEY(id_article) REFERENCES article(id_article)
);
