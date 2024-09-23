# Service d'Analyse NLP

Ce service offre des capacités d'analyse de texte en utilisant des techniques de traitement du langage naturel (NLP). Il est conçu pour être hébergé sur un Raspberry Pi et accessible via une API REST.

## Fonctionnalités

- Analyse de sentiment
- Extraction de mots-clés
- Catégorisation de texte
- Extraction d'entités nommées
- Calcul de score de lisibilité
- Analyse de diversité lexicale
- Extraction de n-grammes
- Calcul de cohérence sémantique
- Analyse de distribution de sentiment

## Prérequis

- Raspberry Pi (testé sur Pi 5)
- Python 3.7+
- pip

## Installation

1. Clonez ce dépôt sur votre Raspberry Pi :
   ```
   git clone [URL_DU_REPO]
   cd [NOM_DU_DOSSIER]
   ```

2. (Optionnel mais recommandé) Créez un environnement virtuel :
   ```
   python3 -m venv venv
   source venv/bin/activate
   ```

3. Installez les dépendances :
   ```
   pip install -r requirements.txt
   ```

4. Téléchargez les ressources NLTK nécessaires :
   ```
   python3 -c "import nltk; nltk.download('punkt'); nltk.download('stopwords')"
   ```

5. Si vous utilisez spaCy, téléchargez le modèle français :
   ```
   python3 -m spacy download fr_core_news_sm
   ```

## Configuration

1. Assurez-vous que le port 5000 est ouvert dans le pare-feu de votre Raspberry Pi :
   ```
   sudo ufw allow 5000
   ```

2. Configurez la redirection de port sur votre routeur pour rediriger le trafic externe vers le port 5000 de votre Raspberry Pi.

## Lancement du service

Pour lancer le service en arrière-plan avec Gunicorn :

```
nohup gunicorn --bind 0.0.0.0:5000 app:app &
```

Pour arrêter le service :
1. Trouvez le PID du processus : `ps aux | grep gunicorn`
2. Arrêtez le processus : `kill [PID]`

## Utilisation de l'API

L'API accepte des requêtes POST à l'endpoint `/analyze`. Le texte à analyser doit être envoyé encodé en base64 dans le corps de la requête JSON.

### Exemple en PHP

```php
<?php
$text = "Voici un exemple de texte à analyser.";
$encodedText = base64_encode($text);

$data = json_encode(["content" => $encodedText]);

$ch = curl_init('http://[VOTRE_IP_OU_DOMAINE]:5000/analyze');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data))
);

$result = curl_exec($ch);
curl_close($ch);

$analysis = json_decode($result, true);
print_r($analysis);
?>
```


```markdown
## Calcul de Similarité Textuelle

Notre API offre une fonctionnalité avancée de calcul de similarité entre plusieurs textes. Cette fonctionnalité permet de comparer jusqu'à 10 textes simultanément, en utilisant différentes méthodes de calcul de similarité.

### Endpoint

`POST /calculate_similarity`

### Paramètres

- `texts` (obligatoire) : Un tableau de chaînes de texte encodées en base64.
- `method` (optionnel) : La méthode de calcul de similarité à utiliser. Options : 'cosine' (par défaut) ou 'jaccard'.
- `ngram_range` (optionnel) : Un tableau de deux entiers spécifiant la plage de n-grammes à utiliser. Par défaut : [1, 1] (unigrammes uniquement).

### Exemple de Requête

```json
{
  "texts": [
    "Q2UgdGV4dGUgZXN0IHVuIGV4ZW1wbGUu",
    "Vm9pY2kgdW4gYXV0cmUgZXhlbXBsZS4=",
    "VW4gdHJvaXNpw6htZSB0ZXh0ZSBkaWZmw6lyZW50Lg=="
  ],
  "method": "cosine",
  "ngram_range": [1, 2]
}
```

### Exemple de Réponse

```json
{
  "text_1_text_2": 0.5,
  "text_1_text_3": 0.3,
  "text_2_text_3": 0.2
}
```

Les scores de similarité sont compris entre 0 (aucune similarité) et 1 (identique). Cette fonctionnalité est particulièrement utile pour l'analyse de corpus de textes, la détection de plagiat, ou la recherche de contenu similaire.
```

```bash
sudo systemctl restart nlp_service
```


```markdown
## Méthodes de Calcul de Similarité Textuelle

Notre API offre plusieurs méthodes pour calculer la similarité entre des textes. Chaque méthode a ses propres caractéristiques, avantages et inconvénients. Voici un aperçu détaillé :

### 1. Similarité Cosinus (Cosine Similarity)

**Description** : Mesure l'angle cosinus entre deux vecteurs de mots dans un espace multidimensionnel.

**Avantages** :
- Insensible à la longueur des documents
- Performante pour les textes de tailles différentes
- Capture bien la similarité sémantique

**Inconvénients** :
- Peut être moins intuitive à interpréter
- Sensible aux mots rares qui peuvent dominer le score

**Exemple** :
```json
{
  "method": "cosine",
  "texts": ["Le chat est noir", "Le chien est noir", "La voiture est rouge"]
}
```
Résultat possible :
```json
{
  "text_1_text_2": 0.75,
  "text_1_text_3": 0.3,
  "text_2_text_3": 0.3
}
```
Ici, les deux premiers textes sont considérés comme plus similaires car ils partagent plus de mots communs.

### 2. Similarité de Jaccard

**Description** : Mesure le rapport entre l'intersection et l'union des ensembles de mots de deux textes.

**Avantages** :
- Simple à comprendre et à interpréter
- Bonne pour comparer des ensembles de mots
- Moins sensible aux mots rares

**Inconvénients** :
- Ne prend pas en compte la fréquence des mots
- Peut être moins précise pour des textes longs

**Exemple** :
```json
{
  "method": "jaccard",
  "texts": ["Le chat est noir", "Le chien est noir", "La voiture est rouge"]
}
```
Résultat possible :
```json
{
  "text_1_text_2": 0.6,
  "text_1_text_3": 0.2,
  "text_2_text_3": 0.2
}
```
La similarité de Jaccard se concentre sur les mots uniques partagés, donnant des scores légèrement différents de la similarité cosinus.

### 3. Utilisation des N-grammes

Les deux méthodes ci-dessus peuvent être utilisées avec des n-grammes, qui sont des séquences de n mots consécutifs.

**Avantages des N-grammes** :
- Prennent en compte l'ordre des mots
- Peuvent capturer des expressions ou des phrases communes

**Inconvénients des N-grammes** :
- Augmentent la complexité du calcul
- Peuvent donner des scores de similarité plus bas

**Exemple avec bi-grammes** :
```json
{
  "method": "cosine",
  "ngram_range": [1, 2],
  "texts": ["Le chat noir dort", "Le chat noir mange", "Le chien blanc court"]
}
```
Résultat possible :
```json
{
  "text_1_text_2": 0.8,
  "text_1_text_3": 0.2,
  "text_2_text_3": 0.2
}
```
L'utilisation de bi-grammes capture la phrase commune "Le chat noir" dans les deux premiers textes, augmentant leur similarité.

### Choisir la Bonne Méthode

- Utilisez la **similarité cosinus** pour une analyse générale, surtout avec des textes de longueurs variées.
- Optez pour la **similarité de Jaccard** si vous vous intéressez principalement au partage de vocabulaire unique.
- Employez les **n-grammes** lorsque l'ordre des mots et les expressions communes sont importants.

Expérimentez avec différentes méthodes et paramètres pour trouver la meilleure approche pour votre cas d'utilisation spécifique.
```


 curl -X POST https://nlpservice.semantic-suggestion.com/api/analyze \
     -H "Content-Type: application/json" \
     -d '{
         "content": "RWxsZSBzb25nZWFpdCBxdWVscXVlZm9pcyBxdWUgY+KAmcOpdGFpZW50IGzDoCBwb3VydGFudCBsZXMgcGx1cyBiZWF1eCBqb3VycyBkZSBzYSB2aWUsIGxhIGx1bmUgZGUgbWllbCwgY29tbWUgb24gZGlzYWl0LiBQb3VyIGVuIGdvw7t0ZXIgbGEgZG91Y2V1ciwgaWwgZcO7dCBmYWxsdSwgc2FucyBkb3V0ZSwgc+KAmWVuIGFsbGVyIHZlcnMgZGVzIHBheXMgw6Agbm9tcyBzb25vcmVzIG/DuSBsZXMgbGVuZGVtYWlucyBkZSBtYXJpYWdlIG9udCBkZSBwbHVzIHN1YXZlcyBwYXJlc3NlcyAhIERhbnMgZGVzIGNoYWlzZXMgZGUgcG9zdGUsIHNvdXMgZGVzIHN0b3JlcyBkZSBzb2llIGJsZXVlLCBvbiBtb250ZSBhdSBwYXMgZGVzIHJvdXRlcyBlc2NhcnDDqWVzLCDDqWNvdXRhbnQgbGEgY2hhbnNvbiBkdSBwb3N0aWxsb24sIHF1aSBzZSByw6lwy6h0ZSBkYW5zIGxhIG1vbnRhZ25lIGF2ZWMgbGVzIGNsb2NoZXR0ZXMgZGVzIGNow6h2cmVzIGV0IGxlIGJydWl0IHNvdXJkIGRlIGxhIGNhc2NhZGUuIFF1YW5kIGxlIHNvbGVpbCBzZSBjb3VjaGUsIG9uIHJlc3BpcmUgYXUgYm9yZCBkZXMgZ29sZmVzIGxlIHBhcmZ1bSBkZXMgY2l0cm9ubmllcnMgOyBwdWlzLCBsZSBzb2lyLCBzdXIgbGEgdGVycmFzc2UgZGVzIHZpbGxhcywgc2V1bHMgZXQgbGVzIGRvaWd0cyBjb25mb25kdXMsIG9uIHJlZ2FyZGUgbGVzIMOpdG9pbGVzIGVuIGZhaXNhbnQgZGVzIHByb2pldHMuIElsIGx1aSBzZW1ibGFpdCBxdWUgY2VydGFpbnMgbGlldXggc3VyIGxhIHRlcnJlIGRldmFpZW50IHByb2R1aXJlIGR1IGJvbmhldXIsIGNvbW1lIHVuZSBwbGFudGUgcGFydGljdWxpw6hyZSBhdSBzb2wgZXQgcXVpIHBvdXNzZSBtYWwgdG91dCBhdXRyZSBwYXJ0LiBRdWUgbmUgcG91dmFpdC1lbGxlIHPigJlhY2NvdWRlciBzdXIgbGUgYmFsY29uIGRlcyBjaGFsZXRzIHN1aXNzZXMgb3UgZW5mZXJtZXIgc2EgdHJpc3Rlc3NlIGRhbnMgdW4gY290dGFnZSDDqWNvc3NhaXMsIGF2ZWMgdW4gbWFyaSB2w6p0dSBk4oCZdW4gaGFiaXQgZGUgdmVsb3VycyBub2lyIMOgIGxvbmd1ZXMgYmFzcXVlcywgZXQgcXVpIHBvcnRlIGRlcyBib3R0ZXMgbW9sbGVzLCB1biBjaGFwZWF1IHBvaW50dSBldCBkZXMgbWFuY2hldHRlcyAhCgpQZXV0LcOqdHJlIGF1cmFpdC1lbGxlIHNvdWhhaXTDqSBmYWlyZSDDoCBxdWVscXUndW4gbGEgY29uZmlkZW5jZSBkZSB0b3V0ZXMgY2VzIGNob3Nlcy4gTWFpcyBjb21tZW50IGRpcmUgdW4gaW5zYWlzaXNzYWJsZSBtYWxhaXNlLCBxdWkgY2hhbmdlIGQnYXNwZWN0IGNvbW1lIGxlcyBudcOpZXMsIHF1aSB0b3VyYmlsbG9ubmUgY29tbWUgbGUgdmVudCA/IExlcyBtb3RzIGx1aSBtYW5xdWFpZW50IGRvbmMsIGwnb2NjYXNpb24sIGxhIGhhcmRpZXNzZS4KU2kgQ2hhcmxlcyBs4oCZYXZhaXQgdm91bHUgY2VwZW5kYW50LCBzJ2lsIHMnZW4gZsO7dCBkb3V0w6ksIHNpIHNvbiByZWdhcmQsIHVuZSBzZXVsZSBmb2lzLCBmw7t0IHZlbnUgw6AgbGEgcmVuY29udHJlIGRlIHNhIHBlbnPDqWUsIGlsIGx1aSBzZW1ibGFpdCBxdSd1bmUgYWJvbmRhbmNlIHN1Yml0ZSBzZSBzZXJhaXQgZMOpdGFjaMOpZSBkZSBzb24gY8OcdXIsIGNvbW1lIHRvbWJlIGxhIHLDqWNvbHRlIGQndW4gZXNwYWxpZXIgcXVhbmQgb24geSBwb3J0ZSBsYSBtYWluLiBNYWlzLCDDoCBtZXN1cmUgcXVlIHNlIHNlcnJhaXQgZGF2YW50YWdlIGwnaW50aW1pdMOpIGRlIGxldXIgdmllLCB1biBkw6l0YWNoZW1lbnQgaW50w6lyaWV1ciBzZSBmYWlzYWl0IHF1aSBsYSBkw6lsaWFpdCBkZSBsdWkuCkxhIGNvbnZlcnNhdGlvbiBkZSBDaGFybGVzIMOpdGFpdCBwbGF0ZSBjb21tZSB1biB0cm90dG9pciBkZSBydWUsIGV0IGxlcyBpZMOpZXMgZGUgdG91dCBsZSBtb25kZSB5IGTDqWZpbGFpZW50IGRhbnMgbGV1ciBjb3N0dW1lIG9yZGluYWlyZSwgc2FucyBleGNpdGVyIGQnw6ltb3Rpb24sIGRlIHJpcmUgb3UgZGUgcsOqdmVyaWUuIElsIG4nYXZhaXQgamFtYWlzIMOpdMOpIGN1cmlldXgsIGRpc2FpdC1pbCwgcGVuZGFudCBxdSdpbCBoYWJpdGFpdCBSb3VlbiwgZCdhbGxlciB2b2lyIGF1IHRow6nDonRyZSBsZXMgYWN0ZXVycyBkZSBQYXJpcy4gSWwgbmUgc2F2YWl0IG5pIG5hZ2VyLCBuaSBmYWlyZSBkZXMgYXJtZXMsIG5pIHRpcmVyIGxlIHBpc3RvbGV0LCBldCBpbCBuZSBwdXQsIHVuIGpvdXIsIGx1aSBleHBsaXF1ZXIgdW4gdGVybWUgZCfDqXF1aXRhdGlvbiBxdSdlbGxlIGF2YWl0IHJlbmNvbnRyw6kgZGFucyB1biByb21hbi4="
     }'


### Format de réponse

La réponse sera un objet JSON contenant les résultats de l'analyse, par exemple :

```json
{
  "sentiment": "POSITIVE",
  "keyphrases": ["exemple", "texte", "analyser"],
  "category": "Général",
  "named_entities": ["..."],
  "readability_score": 65.5,
  "word_count": 7,
  "sentence_count": 1,
  "language": "fr",
  "lexical_diversity": 0.85,
  "top_n_grams": ["..."],
  "semantic_coherence": 0.75,
  "sentiment_distribution": {"positive": 0.8, "neutral": 0.2, "negative": 0}
}
```

# Commandes utiles pour le Raspberry Pi 5

## Environnement virtuel Python

### Activer l'environnement virtuel
```bash
source /home/dietpi/nlpservice/venv/bin/activate
source  venv/bin/activate
```

### Désactiver l'environnement virtuel
```bash
deactivate
```

## Services

### Redémarrer le service NLP
```bash
sudo systemctl restart nlpservice
```

### Vérifier le statut du service NLP
```bash
sudo systemctl status nlpservice
```

### Active le service NLP au démarrage
```bash
sudo systemctl enable nlpservice
```

### Redémarrer Nginx
```bash
sudo systemctl restart nginx
```

### Vérifier le statut de Nginx
```bash
sudo systemctl status nginx
```

## Configuration

### Éditer la configuration Nginx
```bash
sudo nano /etc/nginx/sites-available/nlpservice
```

### Éditer la configuration du service NLP
```bash
sudo nano /etc/systemd/system/nlpservice.service
```

### Recharger les configurations systemd après modification
```bash
sudo systemctl daemon-reload
```

## Logs

### Voir les logs du service NLP
```bash
sudo journalctl -u nlpservice -f
```

### Voir les logs d'erreur Nginx
```bash
sudo tail -f /var/log/nginx/error.log
```

### Voir les logs d'accès Nginx
```bash
sudo tail -f /var/log/nginx/access.log
```

## Gestion des fichiers

### Naviguer vers le répertoire du projet
```bash
cd /home/dietpi/nlpservice
```

### Éditer un fichier Python du projet
```bash
nano app/main.py
```

## Mise à jour du système

### Mettre à jour les paquets
```bash
sudo apt update && sudo apt upgrade -y
```

## Redémarrage du Raspberry Pi

### Redémarrer le Raspberry Pi
```bash
sudo reboot
```

## Arrêt du Raspberry Pi

### Éteindre le Raspberry Pi
```bash
sudo shutdown -h now
```
## Maintenance

- Gardez votre système et les dépendances à jour.
- Surveillez les logs pour détecter d'éventuels problèmes : `tail -f nohup.out`
- Considérez la mise en place d'un système de monitoring pour surveiller la santé du service.

## Support

Pour toute question ou problème, veuillez ouvrir une issue dans le dépôt GitHub du projet.
