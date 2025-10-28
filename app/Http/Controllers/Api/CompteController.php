<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *     title="API Finance - Gestion des Comptes",
 *     version="1.0.0",
 *     description="API RESTful pour la gestion des comptes bancaires"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Serveur de développement"
 * )
 */

class CompteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/v1/comptes",
     *     summary="Lister tous les comptes",
     *     description="Récupère la liste paginée des comptes avec possibilité de filtrage et tri",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"cheque", "courant", "epargne"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "inactif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire ou numéro",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire", "numeroCompte"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                     @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne"}, example="epargne"),
     *                     @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                     @OA\Property(property="devise", type="string", example="XAF"),
     *                     @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                     @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="bloque"),
     *                     @OA\Property(property="motifBlocage", type="string", nullable=true, example="Inactivité de 30+ jours"),
     *                     @OA\Property(property="metadata", type="object",
     *                         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                         @OA\Property(property="version", type="integer", example=1)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=3),
     *                 @OA\Property(property="totalItems", type="integer", example=25),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=true),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="self", type="string", example="/api/v1/comptes?page=1&limit=10"),
     *                 @OA\Property(property="first", type="string", example="/api/v1/comptes?page=1"),
     *                 @OA\Property(property="last", type="string", example="/api/v1/comptes?page=3"),
     *                 @OA\Property(property="next", type="string", nullable=true, example="/api/v1/comptes?page=2"),
     *                 @OA\Property(property="previous", type="string", nullable=true, example="/api/v1/comptes?page=1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Paramètres invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        // Validation des paramètres de requête
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'type' => 'sometimes|in:cheque,courant,epargne',
            'statut' => 'sometimes|in:actif,inactif,bloque,ferme',
            'search' => 'sometimes|string|max:255',
            'sort' => 'sometimes|in:dateCreation,solde,titulaire,numeroCompte',
            'order' => 'sometimes|in:asc,desc'
        ]);

        // Construction de la requête avec filtrage par défaut (seulement comptes actifs)
        $query = Compte::with(['client.user'])->where('statut', 'actif');

        // Filtres
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('statut') && !empty($request->statut)) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numeroCompte', 'ILIKE', "%{$search}%")
                  ->orWhere('titulaire', 'ILIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('numeroCompte', 'ILIKE', "%{$search}%")
                                  ->orWhere('titulaire', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortField) {
            case 'dateCreation':
                $query->orderBy('created_at', $sortOrder);
                break;
            case 'solde':
                $query->orderBy('solde', $sortOrder);
                break;
            case 'titulaire':
                $query->orderBy('titulaire', $sortOrder);
                break;
            case 'numeroCompte':
                $query->orderBy('numeroCompte', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        // Pagination
        $perPage = $request->get('limit', 10);
        $comptes = $query->paginate($perPage);

        // Construction de la réponse
        $response = [
            'success' => true,
            'data' => CompteResource::collection($comptes->items()),
            'pagination' => [
                'currentPage' => $comptes->currentPage(),
                'totalPages' => $comptes->lastPage(),
                'totalItems' => $comptes->total(),
                'itemsPerPage' => $comptes->perPage(),
                'hasNext' => $comptes->hasMorePages(),
                'hasPrevious' => $comptes->currentPage() > 1
            ],
            'links' => [
                'self' => $request->url() . '?' . http_build_query($request->query()),
                'first' => $comptes->url(1),
                'last' => $comptes->url($comptes->lastPage()),
            ]
        ];

        // Ajout des liens next/previous si disponibles
        if ($comptes->hasMorePages()) {
            $response['links']['next'] = $comptes->nextPageUrl();
        }

        if ($comptes->currentPage() > 1) {
            $response['links']['previous'] = $comptes->previousPageUrl();
        }

        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire. Si le client n'existe pas, il est créé automatiquement avec génération de mot de passe et code.",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *             @OA\JsonContent(
     *             required={"type", "soldeInitial", "devise", "client"},
     *             @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", format="float", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", enum={"XAF", "EUR", "USD", "CAD", "GBP"}, example="XAF"),
     *             @OA\Property(
     *                 property="client",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true, example="8f457618-ac42-488b-a2b3-d7d00257ae05"),
     *                 @OA\Property(property="titulaire", type="string", example="Hawa BB Wane"),
     *                 @OA\Property(property="email", type="string", format="email", example="cheikh.sy@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="660f9511-f30c-52e5-b827-557766551111"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123460"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne"}, example="cheque"),
     *                 @OA\Property(property="solde", type="number", format="float", example=500000),
     *                 @OA\Property(property="devise", type="string", example="XAF"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="actif"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="titulaire", type="array", @OA\Items(type="string", example="Le nom du titulaire est requis")),
     *                     @OA\Property(property="soldeInitial", type="array", @OA\Items(type="string", example="Le solde initial doit être supérieur ou égal à 10000"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation métier",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="BUSINESS_RULE_VIOLATION"),
     *                 @OA\Property(property="message", type="string", example="Violation d'une règle métier")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request): JsonResponse
    {
        try {
            // Les données sont déjà validées par StoreCompteRequest
            $validated = $request->validated();

            // Créer ou récupérer le client
            $client = $this->createOrFindClient($validated['client']);

            // Créer le compte
            $compte = $this->createCompte($client, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => CompteResource::make($compte)
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Gestion des erreurs de base de données (unicité, etc.)
            if ($e->getCode() == 23000) { // Violation de contrainte d'unicité
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DUPLICATE_DATA',
                        'message' => 'Un client avec cet email ou téléphone existe déjà',
                        'details' => [
                            'email' => $validated['client']['email'] ?? null,
                            'telephone' => $validated['client']['telephone'] ?? null
                        ]
                    ]
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/comptes/{compteId}",
     *     summary="Récupérer un compte spécifique",
     *     description="Récupère les détails d'un compte spécifique. Admin peut voir tous les comptes, Client seulement les siens.",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID du compte à récupérer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="XAF"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", nullable=true, example="Inactivité de 30+ jours"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="ID invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="INVALID_UUID"),
     *                 @OA\Property(property="message", type="string", example="L'ID du compte doit être un UUID valide"),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="compteId", type="string", example="invalid-id")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="ACCESS_DENIED"),
     *                 @OA\Property(property="message", type="string", example="Vous n'avez pas accès à ce compte")
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $compteId): JsonResponse
    {
        // Validation de l'UUID
        if (!\Illuminate\Support\Str::isUuid($compteId)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_UUID',
                    'message' => 'L\'ID du compte doit être un UUID valide',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 400);
        }

        // Recherche du compte avec relations
        $compte = Compte::with(['client.user'])->find($compteId);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);
        }

        // TODO: Implémenter la logique d'autorisation
        // Pour l'instant, on permet l'accès à tous les comptes
        // Plus tard, vérifier si l'utilisateur est admin ou propriétaire du compte

        return response()->json([
            'success' => true,
            'data' => CompteResource::make($compte)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Méthodes privées pour la logique métier
     */
    private function createOrFindClient(array $clientData): Client
    {
        if (!empty($clientData['id'])) {
            // Essayer de trouver le client existant
            try {
                $client = Client::find($clientData['id']);
                if ($client) {
                    return $client;
                }
            } catch (\Exception $e) {
                // Si l'UUID est invalide, on génère un nouvel UUID
                $clientData['id'] = (string) \Illuminate\Support\Str::uuid();
            }

            // Si le client n'existe pas, on le crée avec l'ID fourni (ou généré)
            // Créer l'utilisateur d'abord
            $password = \Illuminate\Support\Str::random(12);
            $user = User::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $clientData['titulaire'],
                'email' => $clientData['email'],
                'password' => Hash::make($password),
                'email_verified_at' => now()
            ]);

            // Générer un code unique pour le client
            $code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Créer le client avec l'ID fourni (maintenant valide)
            $client = new Client();
            $client->id = $clientData['id'];
            $client->user_id = $user->id;
            $client->numeroCompte = Client::generateNumeroCompte();
            $client->titulaire = $clientData['titulaire'];
            $client->type = 'particulier';
            $client->devise = 'XAF';
            $client->statut = 'actif';
            $client->metadata = [
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'code_authentification' => $code,
                'mot_de_passe_temporaire' => $password,
                'date_creation' => now()->toISOString()
            ];
            $client->save();

            return $client;
        }

        // Si aucun ID n'est fourni, générer un UUID et créer le client
        // Créer l'utilisateur d'abord
        $password = \Illuminate\Support\Str::random(12);
        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $clientData['titulaire'],
            'email' => $clientData['email'],
            'password' => Hash::make($password),
            'email_verified_at' => now()
        ]);

        // Générer un code unique pour le client
        $code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le client avec un UUID généré
        $client = new Client();
        $client->id = (string) \Illuminate\Support\Str::uuid(); // Générer un nouvel UUID
        $client->user_id = $user->id;
        $client->numeroCompte = Client::generateNumeroCompte();
        $client->titulaire = $clientData['titulaire'];
        $client->type = 'particulier';
        $client->devise = 'XAF';
        $client->statut = 'actif';
        $client->metadata = [
            'telephone' => $clientData['telephone'],
            'adresse' => $clientData['adresse'],
            'code_authentification' => $code,
            'mot_de_passe_temporaire' => $password,
            'date_creation' => now()->toISOString()
        ];
        $client->save();

        return $client;

        // Créer l'utilisateur d'abord
        $password = \Illuminate\Support\Str::random(12);
        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $clientData['titulaire'],
            'email' => $clientData['email'],
            'password' => Hash::make($password),
            'email_verified_at' => now()
        ]);

        // Générer un code unique pour le client
        $code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Créer le client
        $client = Client::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'numeroCompte' => Client::generateNumeroCompte(),
            'titulaire' => $clientData['titulaire'],
            'type' => 'particulier',
            'devise' => 'XAF',
            'statut' => 'actif',
            'metadata' => [
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'code_authentification' => $code,
                'mot_de_passe_temporaire' => $password,
                'date_creation' => now()->toISOString()
            ]
        ]);

        return $client;
    }

    private function createCompte(Client $client, array $validated): Compte
    {
        return Compte::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'client_id' => $client->id,
            'numeroCompte' => Compte::generateNumeroCompte(),
            'type' => $validated['type'],
            'devise' => $validated['devise'],
            'statut' => 'actif',
            'solde' => $validated['soldeInitial'],
            'metadata' => [
                'date_creation' => now()->toISOString(),
                'solde_initial' => $validated['soldeInitial'],
                'version' => 1
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
