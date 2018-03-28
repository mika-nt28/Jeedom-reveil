Description
==========
Ce plugin permet de créer des réveils.

Création d'un reveil
==========  

Parametre général
---

![introduction01](../images/ConfigurationGeneral.jpg)   

* Nom  : le nom a déjà été paramétré, mais vous avez la possibilité de le changer.      
* Objet parent : ce paramètre permet d'ajouter l'équipement dans un objet Jeedom.       
* Catégorie : déclare l'équipement dans une catégorie.      
* Visible : permet de rendre l'équipement visible dans le Dashboard.        
* Activer : permet d'activer l'équipement.      

Programation
---
Nous avans la possibilité de cree plusieur programation de reveil.
Pour chaque programation une url de reconfiguration est disponible pour le liée avec d'autre equipement

![introduction01](../images/ConfigurationProgramation.jpg)  

L'url de reprogrammation se presente sous la forme
URL_Jeedom/plugins/reveil/core/api/jeeReveil.php?apikey=APIKEY&id=ID&prog=IDcmd&day=%DAY&heure=%H&minute=%M
Les champs "URL_Jeedom, APIKEY, ID, IDcmd sont automatiquement complété pour chaque URL.
Il sera imperatif de personlaiser cette url en remplace les parametre par les informations a complété :

- %DAY : Les jours de declanchement (0 = Dimanche, 1 = Lundi, ...)
- %H : L'heure de declanchement du reveil
- %M : La minite de declanchement du reveil

Condition
---
Afin de pouvoir filtrer les declanchements du reveil nous avons la possibilité de lui ajouté des conditions d'execution

![introduction01](../images/ConfigurationCondition.jpg)

Cliquer sur "Ajouter une condition" et configurer votre condition
Chaque condition de la liste formera un ET

Action
---
Vous pouvez configurer le sequencement de votre reveil.
Chaque action configurer sera executé dans l'ordre choisi

![introduction01](../images/ConfigurationAction.jpg)
