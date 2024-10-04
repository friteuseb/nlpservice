import requests
import json
import time
import threading

BASE_URL = "https://nlpservice.semantic-suggestion.com/api"

# Fonction pour envoyer une requête à l'API
def send_request(endpoint, method='GET', data=None):
    url = f"{BASE_URL}/{endpoint}"
    try:
        if method == 'GET':
            response = requests.get(url)
        elif method == 'POST':
            response = requests.post(url, json=data)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Erreur lors de la requête à {url}: {str(e)}")
        return None

# Test de performance simple (statut, reset, ajout, recherche)
def basic_test():
    start_time = time.time()

    print("\n1. Vérification du statut initial...")
    status = send_request("faiss_status")
    if not status:
        return "Échec lors de la vérification du statut initial."
    print("Statut initial obtenu.")

    print("\n2. Réinitialisation de l'index FAISS...")
    reset_result = send_request("reset_faiss_index", method='POST')
    if reset_result:
        print("Index réinitialisé avec succès.")
    else:
        return "Échec lors de la réinitialisation de l'index."

    print("\n3. Ajout de textes dans l'index FAISS...")
    texts = [
        {"id": "1", "text": "L'intelligence artificielle transforme notre monde."},
        {"id": "2", "text": "Les algorithmes de machine learning sont de plus en plus sophistiqués."},
        {"id": "3", "text": "Le deep learning révolutionne la reconnaissance d'images."},
        {"id": "4", "text": "Le traitement du langage naturel améliore les assistants virtuels."},
        {"id": "5", "text": "L'apprentissage par renforcement est utilisé dans les jeux et la robotique."}
    ]
    add_result = send_request("add_texts", method='POST', data={"items": texts})
    if add_result:
        print("Textes ajoutés avec succès.")
    else:
        return "Échec lors de l'ajout des textes."

    print("\n4. Recherche de textes similaires...")
    similar_result = send_request("find_similar", method='POST', data={"id": "1", "k": 3})
    if similar_result:
        print("Recherche de textes similaires effectuée avec succès.")
    else:
        return "Échec lors de la recherche de textes similaires."

    end_time = time.time()
    return f"Test basique terminé en {end_time - start_time:.2f} secondes."

# Test avec plusieurs requêtes séquentielles
def multiple_requests_test(num_requests=10):
    start_time = time.time()
    print(f"\nEnvoi de {num_requests} requêtes séquentielles...")

    for i in range(1, num_requests + 1):
        print(f"  - Requête {i} sur {num_requests}...")
        send_request("find_similar", method='POST', data={"id": "1", "k": 3})
        time.sleep(0.1)  # Pause pour éviter la surcharge

    end_time = time.time()
    return f"Test avec {num_requests} requêtes séquentielles terminé en {end_time - start_time:.2f} secondes."

# Test avec des requêtes parallèles
def parallel_requests_test(num_threads=10):
    def send_similar_request_threaded(id, k):
        send_request("find_similar", method='POST', data={"id": id, "k": k})
    
    start_time = time.time()
    threads = []
    print(f"\nLancement de {num_threads} requêtes parallèles...")

    for i in range(1, num_threads + 1):
        print(f"  - Démarrage de la requête parallèle {i}...")
        thread = threading.Thread(target=send_similar_request_threaded, args=(str(i), 3))
        threads.append(thread)
        thread.start()

    for thread in threads:
        thread.join()

    end_time = time.time()
    return f"Test avec {num_threads} requêtes parallèles terminé en {end_time - start_time:.2f} secondes."

# Menu interactif
def show_menu():
    print("\n=== Menu des Tests de Performance ===")
    print("1. Test basique (statut, reset, ajout, recherche)")
    print("2. Test avec plusieurs requêtes séquentielles")
    print("3. Test avec des requêtes parallèles")
    print("0. Quitter")
    return input("Veuillez choisir une option : ")

# Fonction principale
def run_tests():
    while True:
        choice = show_menu()
        if choice == '1':
            result = basic_test()
        elif choice == '2':
            num_requests = int(input("Entrez le nombre de requêtes séquentielles : "))
            result = multiple_requests_test(num_requests)
        elif choice == '3':
            num_threads = int(input("Entrez le nombre de requêtes parallèles : "))
            result = parallel_requests_test(num_threads)
        elif choice == '0':
            print("Merci d'avoir utilisé le testeur de performance.")
            break
        else:
            print("Choix invalide, veuillez réessayer.")
            continue

        print("\n=== Compte Rendu du Test ===")
        print(result)
        print("\n============================")

if __name__ == "__main__":
    run_tests()
