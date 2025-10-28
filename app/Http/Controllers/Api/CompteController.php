<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     *         name="scope",
     *         in="query",
     *         description="Portée des résultats (local/global)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"local", "global"}, default="global")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"courant", "epargne", "titre", "devise"})
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
     *                     @OA\Property(property="type", type="string", enum={"courant", "epargne", "titre", "devise"}, example="epargne"),
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
            'scope' => 'sometimes|in:local,global',
            'type' => 'sometimes|in:courant,epargne,titre,devise',
            'statut' => 'sometimes|in:actif,inactif,bloque,ferme',
            'search' => 'sometimes|string|max:255',
            'sort' => 'sometimes|in:dateCreation,solde,titulaire,numeroCompte',
            'order' => 'sometimes|in:asc,desc'
        ]);

        // Construction de la requête avec filtrage par défaut
        $query = Compte::with(['client.user']);

        // Application du scope depuis le modèle
        $scope = $request->get('scope', 'global');
        $query->applyScope($scope);

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
