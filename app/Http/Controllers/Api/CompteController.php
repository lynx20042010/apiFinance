<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Requests\UpdateCompteRequest;
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
 *     description="API RESTful pour la gestion des comptes bancaires et transactions financières"
 * )
 * @OA\Server(
 *     url="https://apifinance.onrender.com/api",
 *     description="Serveur de production"
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
     *     description="Récupère les détails d'un compte spécifique par ID UUID ou numéro de compte. Admin peut voir tous les comptes, Client seulement les siens.",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à récupérer",
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
        // Recherche du compte par numéro de compte uniquement
        $compte = Compte::with(['client.user'])->where('numeroCompte', $compteId)->first();

        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec le numéro spécifié n\'existe pas',
                    'details' => [
                        'numeroCompte' => $compteId
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
     * @OA\Put(
     *     path="/v1/comptes/{compteId}",
     *     summary="Mettre à jour un compte",
     *     description="Met à jour les informations d'un compte existant. Seuls certains champs peuvent être modifiés pour des raisons de sécurité.",
     *     operationId="updateCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne", "titre", "devise"}, example="courant"),
     *             @OA\Property(property="devise", type="string", enum={"XAF", "EUR", "USD", "CAD", "GBP"}, example="EUR"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="actif"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="agence", type="string", maxLength=100, example="Agence Centrale Dakar"),
     *                 @OA\Property(property="rib", type="string", maxLength=50, example="123456789012345678901234567890"),
     *                 @OA\Property(property="iban", type="string", maxLength=50, example="SN12 3456 7890 1234 5678 9012 345")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "courant", "epargne", "titre", "devise"}, example="courant"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="EUR"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "inactif", "bloque", "ferme"}, example="actif"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=2)
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides ou changement non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Opération non autorisée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="OPERATION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Cette opération n'est pas autorisée sur ce compte")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateCompteRequest $request, string $compteId): JsonResponse
    {
        // Recherche du compte par numéro de compte uniquement
        $compte = Compte::where('numeroCompte', $compteId)->first();

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

        // Vérifications de sécurité avant mise à jour
        $validated = $request->validated();

        // Vérifier si le compte peut être modifié (pas fermé définitivement)
        if ($compte->statut === 'ferme') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OPERATION_NOT_ALLOWED',
                    'message' => 'Impossible de modifier un compte fermé',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 403);
        }

        // Vérifier les changements de devise (seulement si solde = 0)
        if (isset($validated['devise']) && $validated['devise'] !== $compte->devise) {
            if ($compte->solde > 0) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'OPERATION_NOT_ALLOWED',
                        'message' => 'Impossible de changer la devise d\'un compte avec un solde positif',
                        'details' => [
                            'compteId' => $compteId,
                            'soldeActuel' => $compte->solde,
                            'deviseActuelle' => $compte->devise,
                            'nouvelleDevise' => $validated['devise']
                        ]
                    ]
                ], 403);
            }
        }

        try {
            // Mise à jour du compte
            $compte->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Compte mis à jour avec succès',
                'data' => CompteResource::make($compte)
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Gestion des erreurs de base de données
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Erreur lors de la mise à jour du compte',
                    'details' => [
                        'compteId' => $compteId,
                        'error' => $e->getMessage()
                    ]
                ]
            ], 500);
        }
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
     * @OA\Post(
     *     path="/v1/comptes/{compteId}/block",
     *     summary="Bloquer un compte épargne",
     *     description="Bloque un compte bancaire de type épargne. Cette opération n'est autorisée que pour les comptes épargne.",
     *     operationId="blockCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à bloquer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif", type="string", maxLength=255, example="Suspicion d'activité frauduleuse"),
     *             @OA\Property(property="dureeBlocage", type="integer", minimum=1, maximum=365, example=30, description="Durée en jours (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CPT2025000001"),
     *                 @OA\Property(property="type", type="string", example="epargne"),
     *                 @OA\Property(property="statut", type="string", example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", example="Suspicion d'activité frauduleuse"),
     *                 @OA\Property(property="dateBlocage", type="string", format="date-time", example="2023-06-10T14:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Opération non autorisée pour ce type de compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="OPERATION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Le blocage n'est autorisé que pour les comptes épargne")
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Compte déjà bloqué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_ALREADY_BLOCKED"),
     *                 @OA\Property(property="message", type="string", example="Le compte est déjà bloqué")
     *             )
     *         )
     *     )
     * )
     */
    public function block(Request $request, string $compteId): JsonResponse
    {
        // Validation des données d'entrée
        $request->validate([
            'motif' => 'required|string|max:255',
            'dureeBlocage' => 'sometimes|integer|min:1|max:365'
        ], [
            'motif.required' => 'Le motif de blocage est obligatoire.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.',
            'dureeBlocage.integer' => 'La durée doit être un nombre entier.',
            'dureeBlocage.min' => 'La durée minimale est de 1 jour.',
            'dureeBlocage.max' => 'La durée maximale est de 365 jours.'
        ]);

        // Recherche du compte par ID UUID ou numéro de compte
        $compte = null;

        // Essayer d'abord comme UUID
        if (\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::find($compteId);
        }

        // Si pas trouvé et que ce n'est pas un UUID, essayer comme numéro de compte
        if (!$compte && !\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::where('numeroCompte', $compteId)->first();
        }

        // Si toujours pas trouvé, essayer avec un UUID même si ce n'est pas un format valide
        if (!$compte) {
            $compte = Compte::find($compteId);
        }

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

        // Vérifier que c'est un compte épargne
        if ($compte->type !== 'epargne') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OPERATION_NOT_ALLOWED',
                    'message' => 'Le blocage n\'est autorisé que pour les comptes épargne',
                    'details' => [
                        'compteId' => $compteId,
                        'type' => $compte->type,
                        'typeRequis' => 'epargne'
                    ]
                ]
            ], 400);
        }

        // Vérifier que le compte n'est pas déjà bloqué
        if ($compte->statut === 'bloque') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_ALREADY_BLOCKED',
                    'message' => 'Le compte est déjà bloqué',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 409);
        }

        // Calculer la date de fin de blocage si durée spécifiée
        $dateFinBlocage = null;
        if ($request->has('dureeBlocage')) {
            $dateFinBlocage = now()->addDays($request->dureeBlocage);
        }

        // Mettre à jour le compte
        $compte->update([
            'statut' => 'bloque',
            'metadata' => array_merge($compte->metadata ?? [], [
                'motifBlocage' => $request->motif,
                'dateBlocage' => now()->toISOString(),
                'dureeBlocage' => $request->dureeBlocage ?? null,
                'dateFinBlocage' => $dateFinBlocage?->toISOString(),
                'statutAvantBlocage' => $compte->statut
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte bloqué avec succès',
            'data' => [
                'compteId' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'motifBlocage' => $request->motif,
                'dateBlocage' => now()->toISOString(),
                'dureeBlocage' => $request->dureeBlocage ?? null,
                'dateFinBlocage' => $dateFinBlocage?->toISOString()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes/{compteId}/unblock",
     *     summary="Débloquer un compte épargne",
     *     description="Débloque un compte bancaire de type épargne précédemment bloqué.",
     *     operationId="unblockCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à débloquer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif", type="string", maxLength=255, example="Blocage levé suite à vérification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte débloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte débloqué avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CPT2025000001"),
     *                 @OA\Property(property="type", type="string", example="epargne"),
     *                 @OA\Property(property="statut", type="string", example="actif"),
     *                 @OA\Property(property="dateDeblocage", type="string", format="date-time", example="2023-06-10T14:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Opération non autorisée pour ce type de compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="OPERATION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Le déblocage n'est autorisé que pour les comptes épargne")
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Compte non bloqué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_BLOCKED"),
     *                 @OA\Property(property="message", type="string", example="Le compte n'est pas bloqué")
     *             )
     *         )
     *     )
     * )
     */
    public function unblock(Request $request, string $compteId): JsonResponse
    {
        // Validation des données d'entrée
        $request->validate([
            'motif' => 'required|string|max:255'
        ], [
            'motif.required' => 'Le motif de déblocage est obligatoire.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.'
        ]);

        // Recherche du compte par numéro de compte uniquement
        $compte = Compte::where('numeroCompte', $compteId)->first();

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

        // Vérifier que c'est un compte épargne
        if ($compte->type !== 'epargne') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OPERATION_NOT_ALLOWED',
                    'message' => 'Le déblocage n\'est autorisé que pour les comptes épargne',
                    'details' => [
                        'compteId' => $compteId,
                        'type' => $compte->type,
                        'typeRequis' => 'epargne'
                    ]
                ]
            ], 400);
        }

        // Vérifier que le compte est bloqué
        if ($compte->statut !== 'bloque') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_BLOCKED',
                    'message' => 'Le compte n\'est pas bloqué',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 409);
        }

        // Récupérer le statut avant blocage depuis les métadonnées
        $statutAvantBlocage = $compte->metadata['statutAvantBlocage'] ?? 'actif';

        // Mettre à jour le compte
        $metadata = $compte->metadata ?? [];
        $metadata['motifDeblocage'] = $request->motif;
        $metadata['dateDeblocage'] = now()->toISOString();
        unset($metadata['motifBlocage'], $metadata['dateBlocage'], $metadata['dureeBlocage'], $metadata['dateFinBlocage'], $metadata['statutAvantBlocage']);

        $compte->update([
            'statut' => $statutAvantBlocage,
            'metadata' => $metadata
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte débloqué avec succès',
            'data' => [
                'compteId' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'dateDeblocage' => now()->toISOString()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes/{compteId}/archive",
     *     summary="Archiver un compte fermé",
     *     description="Archive un compte bancaire fermé. Cette opération n'est autorisée que pour les comptes ayant le statut 'ferme'. L'archivage permet de conserver l'historique sans afficher le compte dans les listes actives.",
     *     operationId="archiveCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à archiver",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif", type="string", maxLength=255, example="Archivage suite à clôture définitive du compte"),
     *             @OA\Property(property="dureeArchivage", type="integer", minimum=365, maximum=2555, example=1825, description="Durée d'archivage en jours (minimum 1 an, maximum 7 ans)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte archivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte archivé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="type", type="string", example="courant"),
     *                 @OA\Property(property="statut", type="string", example="archive"),
     *                 @OA\Property(property="motifArchivage", type="string", example="Archivage suite à clôture définitive du compte"),
     *                 @OA\Property(property="dateArchivage", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                 @OA\Property(property="dateFinArchivage", type="string", format="date-time", example="2028-06-10T14:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Opération non autorisée pour ce type de compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="OPERATION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="L'archivage n'est autorisé que pour les comptes fermés")
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Archivage impossible",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="ARCHIVE_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Le compte ne peut pas être archivé car il n'est pas fermé")
     *             )
     *         )
     *     )
     * )
     */
    public function archive(Request $request, string $compteId): JsonResponse
    {
        // Validation des données d'entrée
        $request->validate([
            'motif' => 'required|string|max:255',
            'dureeArchivage' => 'sometimes|integer|min:365|max:2555' // 1 an à 7 ans
        ], [
            'motif.required' => 'Le motif d\'archivage est obligatoire.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.',
            'dureeArchivage.integer' => 'La durée doit être un nombre entier.',
            'dureeArchivage.min' => 'La durée minimale d\'archivage est de 1 an (365 jours).',
            'dureeArchivage.max' => 'La durée maximale d\'archivage est de 7 ans (2555 jours).'
        ]);

        // Recherche du compte par ID UUID ou numéro de compte
        $compte = null;

        // Essayer d'abord comme UUID
        if (\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::find($compteId);
        }

        // Si pas trouvé et que ce n'est pas un UUID, essayer comme numéro de compte
        if (!$compte && !\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::where('numeroCompte', $compteId)->first();
        }

        // Si toujours pas trouvé, essayer avec un UUID même si ce n'est pas un format valide
        if (!$compte) {
            $compte = Compte::find($compteId);
        }

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

        // Vérifier que le compte est fermé
        if ($compte->statut !== 'ferme') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OPERATION_NOT_ALLOWED',
                    'message' => 'L\'archivage n\'est autorisé que pour les comptes fermés',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut,
                        'statutRequis' => 'ferme'
                    ]
                ]
            ], 400);
        }

        // Vérifier que le compte n'est pas déjà archivé
        if ($compte->statut === 'archive') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ARCHIVE_NOT_ALLOWED',
                    'message' => 'Le compte est déjà archivé',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 409);
        }

        // Calculer la date de fin d'archivage (défaut 5 ans si non spécifié)
        $dureeArchivage = $request->dureeArchivage ?? 1825; // 5 ans par défaut
        $dateFinArchivage = now()->addDays($dureeArchivage);

        // Mettre à jour le compte
        $compte->update([
            'statut' => 'archive',
            'metadata' => array_merge($compte->metadata ?? [], [
                'motifArchivage' => $request->motif,
                'dateArchivage' => now()->toISOString(),
                'dureeArchivage' => $dureeArchivage,
                'dateFinArchivage' => $dateFinArchivage->toISOString(),
                'statutAvantArchivage' => $compte->statut
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte archivé avec succès',
            'data' => [
                'compteId' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'motifArchivage' => $request->motif,
                'dateArchivage' => now()->toISOString(),
                'dureeArchivage' => $dureeArchivage,
                'dateFinArchivage' => $dateFinArchivage->toISOString()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/comptes/{compteId}/unarchive",
     *     summary="Désarchiver un compte",
     *     description="Désarchive un compte bancaire précédemment archivé, le remettant dans son état antérieur.",
     *     operationId="unarchiveCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à désarchiver",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif", type="string", maxLength=255, example="Désarchivage suite à demande du client")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte désarchivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte désarchivé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="type", type="string", example="courant"),
     *                 @OA\Property(property="statut", type="string", example="ferme"),
     *                 @OA\Property(property="dateDesarchivage", type="string", format="date-time", example="2023-06-10T14:30:00Z")
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Désarchivage impossible",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="UNARCHIVE_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Le compte n'est pas archivé")
     *             )
     *         )
     *     )
     * )
     */
    public function unarchive(Request $request, string $compteId): JsonResponse
    {
        // Validation des données d'entrée
        $request->validate([
            'motif' => 'required|string|max:255'
        ], [
            'motif.required' => 'Le motif de désarchivage est obligatoire.',
            'motif.max' => 'Le motif ne peut pas dépasser 255 caractères.'
        ]);

        // Recherche du compte par ID UUID ou numéro de compte
        $compte = null;

        // Essayer d'abord comme UUID
        if (\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::find($compteId);
        }

        // Si pas trouvé et que ce n'est pas un UUID, essayer comme numéro de compte
        if (!$compte && !\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::where('numeroCompte', $compteId)->first();
        }

        // Si toujours pas trouvé, essayer avec un UUID même si ce n'est pas un format valide
        if (!$compte) {
            $compte = Compte::find($compteId);
        }

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

        // Vérifier que le compte est archivé
        if ($compte->statut !== 'archive') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNARCHIVE_NOT_ALLOWED',
                    'message' => 'Le compte n\'est pas archivé',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 409);
        }

        // Récupérer le statut avant archivage depuis les métadonnées
        $statutAvantArchivage = $compte->metadata['statutAvantArchivage'] ?? 'ferme';

        // Mettre à jour le compte
        $metadata = $compte->metadata ?? [];
        $metadata['motifDesarchivage'] = $request->motif;
        $metadata['dateDesarchivage'] = now()->toISOString();
        unset($metadata['motifArchivage'], $metadata['dateArchivage'], $metadata['dureeArchivage'], $metadata['dateFinArchivage'], $metadata['statutAvantArchivage']);

        $compte->update([
            'statut' => $statutAvantArchivage,
            'metadata' => $metadata
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Compte désarchivé avec succès',
            'data' => [
                'compteId' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'type' => $compte->type,
                'statut' => $compte->statut,
                'dateDesarchivage' => now()->toISOString()
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/comptes/{compteId}",
     *     summary="Supprimer un compte",
     *     description="Supprime définitivement un compte bancaire. Cette opération est irréversible et nécessite que le compte soit vide de solde et sans transactions actives.",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         description="ID UUID ou numéro de compte à supprimer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="compteId", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="deletedAt", type="string", format="date-time", example="2023-06-10T14:30:00Z")
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
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
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
     *                 @OA\Property(property="message", type="string", example="L'ID du compte doit être un UUID valide")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Suppression impossible",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="DELETION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Le compte ne peut pas être supprimé car il contient encore des fonds ou des transactions actives")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Opération non autorisée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="error",
     *                 type="object",
     *                 @OA\Property(property="code", type="string", example="OPERATION_NOT_ALLOWED"),
     *                 @OA\Property(property="message", type="string", example="Cette opération n'est pas autorisée sur ce compte")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(string $compteId): JsonResponse
    {
        // Recherche du compte par ID UUID ou numéro de compte
        $compte = null;

        // Essayer d'abord comme UUID
        if (\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::with(['client.user', 'transactions'])->find($compteId);
        }

        // Si pas trouvé et que ce n'est pas un UUID, essayer comme numéro de compte
        if (!$compte && !\Illuminate\Support\Str::isUuid($compteId)) {
            $compte = Compte::with(['client.user', 'transactions'])->where('numeroCompte', $compteId)->first();
        }

        // Si toujours pas trouvé, essayer avec un UUID même si ce n'est pas un format valide
        if (!$compte) {
            $compte = Compte::with(['client.user', 'transactions'])->find($compteId);
        }

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

        // Vérifications de sécurité avant suppression
        if ($compte->statut === 'ferme') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OPERATION_NOT_ALLOWED',
                    'message' => 'Impossible de supprimer un compte déjà fermé',
                    'details' => [
                        'compteId' => $compteId,
                        'statut' => $compte->statut
                    ]
                ]
            ], 403);
        }

        // Note: La suppression est autorisée même avec un solde positif
        // selon les nouvelles règles métier

        // Vérifier s'il y a des transactions actives (non traitées)
        $transactionsActives = $compte->transactions()
            ->whereIn('statut', ['en_attente', 'traitee'])
            ->count();

        if ($transactionsActives > 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DELETION_NOT_ALLOWED',
                    'message' => 'Impossible de supprimer un compte avec des transactions actives',
                    'details' => [
                        'compteId' => $compteId,
                        'transactionsActives' => $transactionsActives
                    ]
                ]
            ], 409);
        }

        // Stocker les informations avant suppression pour la réponse
        $compteInfo = [
            'compteId' => $compte->id,
            'numeroCompte' => $compte->numeroCompte,
            'titulaire' => $compte->client->titulaire ?? 'N/A',
            'deletedAt' => now()->toISOString()
        ];

        try {
            // Suppression du compte (les relations sont gérées par les clés étrangères)
            $compte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès',
                'data' => $compteInfo
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Gestion des erreurs de base de données
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Erreur lors de la suppression du compte',
                    'details' => [
                        'compteId' => $compteId,
                        'error' => $e->getMessage()
                    ]
                ]
            ], 500);
        }
    }
}
