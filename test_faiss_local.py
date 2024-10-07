import requests
import json
import time
import threading
import os
from dotenv import load_dotenv
import statistics

# Charger les variables d'environnement
load_dotenv()
API_KEY = os.getenv('API_KEY')
BASE_URL = "https://nlpservice.semantic-suggestion.com/api"

def send_request(endpoint, method='GET', data=None):
    url = f"{BASE_URL}/{endpoint}"
    headers = {"X-API-Key": API_KEY}
    start_time = time.time()
    try:
        if method == 'GET':
            response = requests.get(url, headers=headers)
        elif method == 'POST':
            response = requests.post(url, json=data, headers=headers)
        response.raise_for_status()
        end_time = time.time()
        return {
            "success": True,
            "data": response.json(),
            "status_code": response.status_code,
            "response_time": end_time - start_time
        }
    except requests.exceptions.RequestException as e:
        end_time = time.time()
        return {
            "success": False,
            "error": str(e),
            "status_code": getattr(e.response, 'status_code', None),
            "response_time": end_time - start_time
        }

# Textes réalistes de 300 mots environ
def realistic_texts():
    return [
        {"id": "1", "text": "L'intelligence artificielle (IA) est en train de transformer de nombreux secteurs industriels, "
         "de la santé à l'automobile, en passant par la finance. L'apprentissage automatique, une sous-branche de l'IA, "
         "permet de développer des modèles capables d'apprendre et de s'améliorer à partir des données sans être explicitement "
         "programmés. Les applications de l'IA vont des diagnostics médicaux à la conduite autonome, en passant par les algorithmes "
         "de recommandation utilisés par des géants du numérique comme Netflix ou Amazon. Cependant, l'IA pose aussi des défis "
         "éthiques importants, notamment en ce qui concerne la vie privée, la transparence des décisions algorithmiques et les biais "
         "inhérents aux données utilisées pour entraîner ces modèles. Alors que les entreprises et les gouvernements explorent les "
         "potentiels de l'IA, il est essentiel de mettre en place des cadres réglementaires robustes pour garantir une utilisation "
         "responsable et éthique de ces technologies."},
        
        {"id": "2", "text": "Le changement climatique est l'un des défis mondiaux les plus pressants auxquels l'humanité est confrontée "
         "aujourd'hui. Les scientifiques s'accordent à dire que les émissions de gaz à effet de serre d'origine humaine, principalement "
         "le dioxyde de carbone et le méthane, sont les principales causes du réchauffement planétaire. Si des mesures drastiques ne sont "
         "pas prises rapidement pour limiter ces émissions, les conséquences pourraient être catastrophiques, avec des phénomènes météorologiques "
         "extrêmes, une élévation du niveau des mers et des pertes massives de biodiversité. Des solutions existent pourtant pour atténuer le "
         "changement climatique, telles que l'adoption des énergies renouvelables, la protection des forêts et la transition vers des systèmes "
         "alimentaires plus durables. Les gouvernements, les entreprises et les citoyens ont tous un rôle à jouer dans la lutte contre ce défi global."},
        
        {"id": "3", "text": "La révolution numérique a bouleversé nos modes de vie, transformant non seulement la manière dont nous communiquons, "
         "mais aussi celle dont nous travaillons, apprenons et consommons. L'accès à l'information est devenu instantané, les réseaux sociaux "
         "permettant de connecter des individus et des communautés du monde entier. Cependant, cette ère numérique apporte également son lot de "
         "défis, notamment en termes de protection des données personnelles, de cybercriminalité et de désinformation. Les entreprises technologiques "
         "sont désormais au centre de ces problématiques, et les régulateurs du monde entier cherchent à encadrer l'utilisation des technologies afin "
         "d'assurer un équilibre entre innovation et respect des droits des utilisateurs. Dans ce contexte, l'éducation aux compétences numériques et "
         "la maîtrise des outils technologiques deviennent essentielles pour naviguer dans un monde de plus en plus connecté."},
        
        {"id": "4", "text": "L'exploration spatiale continue de captiver l'imagination des scientifiques et du grand public. Depuis les premiers pas "
         "de l'homme sur la Lune, les avancées technologiques ont permis de repousser les limites de l'exploration humaine et robotique de l'espace. "
         "Des missions récentes, telles que l'atterrissage du rover Perseverance sur Mars ou l'envoi de sondes à destination de planètes lointaines, "
         "ouvrent la voie à une meilleure compréhension de notre système solaire. Les projets de colonisation de la Lune ou de Mars ne sont plus de la "
         "science-fiction, mais des ambitions concrètes portées par des agences spatiales comme la NASA et des entreprises privées comme SpaceX. "
         "Cependant, ces projets soulèvent également des questions sur la viabilité de la vie humaine dans des environnements extraterrestres et les "
         "impacts éthiques de l'exploration et de la colonisation de nouveaux mondes."}
    ]

def generate_detailed_report(test_name, results):
    print(f"\n=== Rapport détaillé pour {test_name} ===")
    success_rate = sum(1 for r in results if r['success']) / len(results) * 100
    avg_response_time = statistics.mean(r['response_time'] for r in results)
    
    print(f"Taux de succès: {success_rate:.2f}%")
    print(f"Temps de réponse moyen: {avg_response_time:.4f} secondes")
    
    if results:
        print("\nDétails des requêtes:")
        for i, result in enumerate(results, 1):
            print(f"  Requête {i}:")
            print(f"    Succès: {'Oui' if result['success'] else 'Non'}")
            print(f"    Temps de réponse: {result['response_time']:.4f} secondes")
            print(f"    Code de statut: {result['status_code']}")
            if result['success']:
                print(f"    Données reçues: {json.dumps(result['data'], indent=2)}")
            else:
                print(f"    Erreur: {result['error']}")
            print()

def basic_test():
    results = []
    print("\nExécution du test basique...")
    
    # 1. Vérification du statut initial
    print("1. Vérification du statut initial...")
    status_result = send_request("faiss_status")
    results.append(status_result)
    
    # 2. Réinitialisation de l'index FAISS
    print("2. Réinitialisation de l'index FAISS...")
    reset_result = send_request("reset_faiss_index", method='POST')
    results.append(reset_result)
    
    # 3. Ajout de textes réalistes
    print("3. Ajout de textes réalistes dans l'index FAISS...")
    texts = realistic_texts()
    add_result = send_request("add_texts", method='POST', data={"items": texts})
    results.append(add_result)
    
    # 4. Recherche de textes similaires
    print("4. Recherche de textes similaires...")
    similar_result = send_request("find_similar", method='POST', data={"id": "1", "k": 3})
    results.append(similar_result)
    
    generate_detailed_report("Test Basique", results)

def multiple_requests_test(num_requests=10):
    results = []
    print(f"\nEnvoi de {num_requests} requêtes séquentielles...")
    
    for i in range(1, num_requests + 1):
        print(f"  - Requête {i} sur {num_requests}...")
        result = send_request("find_similar", method='POST', data={"id": "1", "k": 3})
        results.append(result)
        time.sleep(0.1)  # Pause pour éviter la surcharge
    
    generate_detailed_report(f"Test avec {num_requests} requêtes séquentielles", results)

def parallel_requests_test(num_threads=10):
    results = []
    threads = []
    
    def send_similar_request_threaded(id, k):
        result = send_request("find_similar", method='POST', data={"id": id, "k": k})
        results.append(result)
    
    print(f"\nLancement de {num_threads} requêtes parallèles...")
    
    for i in range(1, num_threads + 1):
        print(f"  - Démarrage de la requête parallèle {i}...")
        thread = threading.Thread(target=send_similar_request_threaded, args=(str(i), 3))
        threads.append(thread)
        thread.start()
    
    for thread in threads:
        thread.join()
    
    generate_detailed_report(f"Test avec {num_threads} requêtes parallèles", results)

# Menu interactif avec explications détaillées
def show_menu():
    print("\n=== Menu des Tests de Performance ===")
    print("1. Test basique (statut, reset, ajout, recherche)")
    print("   - Ce test effectue une série d'actions basiques :")
    print("     * Vérification du statut de FAISS pour voir si l'index est initialisé.")
    print("     * Réinitialisation de l'index FAISS pour supprimer toutes les données actuelles.")
    print("     * Ajout de plusieurs textes réalistes (environ 300 mots chacun) à l'index FAISS.")
    print("     * Recherche de textes similaires dans l'index pour un texte donné.")
    print("   - Ce test permet de s'assurer que toutes les fonctionnalités basiques de l'API fonctionnent correctement.")
    
    print("\n2. Test avec plusieurs requêtes séquentielles")
    print("   - Ce test envoie un nombre défini de requêtes séquentielles à l'API pour rechercher des textes similaires.")
    print("   - Les requêtes sont envoyées une par une, avec un court délai entre chacune pour éviter de surcharger le serveur.")
    print("   - Ce test est utile pour mesurer les performances de l'API lorsque plusieurs requêtes sont envoyées en succession.")

    print("\n3. Test avec des requêtes parallèles")
    print("   - Ce test lance plusieurs requêtes en parallèle pour rechercher des textes similaires.")
    print("   - Les requêtes sont exécutées simultanément, ce qui permet de tester la capacité de l'API à gérer des requêtes concurrentes.")
    print("   - Ce test est utile pour évaluer la robustesse de l'API en situation de charge élevée, lorsque plusieurs utilisateurs effectuent des requêtes en même temps.")

    print("\n0. Quitter")
    return input("Veuillez choisir une option : ")

def run_tests():
    while True:
        choice = show_menu()
        if choice == '1':
            basic_test()
        elif choice == '2':
            num_requests = int(input("Entrez le nombre de requêtes séquentielles : "))
            multiple_requests_test(num_requests)
        elif choice == '3':
            num_threads = int(input("Entrez le nombre de requêtes parallèles : "))
            parallel_requests_test(num_threads)
        elif choice == '0':
            print("Merci d'avoir utilisé le testeur de performance.")
            break
        else:
            print("Choix invalide, veuillez réessayer.")
            continue

if __name__ == "__main__":
    run_tests()