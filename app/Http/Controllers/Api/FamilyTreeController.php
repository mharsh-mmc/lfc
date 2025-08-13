<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyTreeNode;
use App\Models\FamilyTreeEdge;
use App\Models\FamilyTreeLayout;
use App\Models\User;
use App\Services\TreeLayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FamilyTreeController extends Controller
{
    protected $treeLayoutService;

    public function __construct(TreeLayoutService $treeLayoutService)
    {
        $this->treeLayoutService = $treeLayoutService;
    }

    /**
     * Get family tree data for a user
     */
    public function getTree($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        
        // Check if user can view this tree
        if (!$user->is_public && Auth::id() !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nodes = FamilyTreeNode::where('user_id', $userId)
            ->with('profile:id,name,username,profile_photo_path,date_of_birth,location,bio,profession,passion')
            ->get();

        // If no nodes exist, create the central node for the user
        if ($nodes->isEmpty()) {
            $this->createCentralNode($userId, $user);
            $nodes = FamilyTreeNode::where('user_id', $userId)
                ->with('profile:id,name,username,profile_photo_path,date_of_birth,location,bio,profession,passion')
                ->get();
        }

        $edges = FamilyTreeEdge::where('user_id', $userId)->get();

        // Convert edges to Vue Flow format
        $vueFlowEdges = $edges->map(function($edge) {
            return [
                'id' => 'e' . $edge->from_node_id . '-' . $edge->to_node_id,
                'source' => (string)$edge->from_node_id,
                'target' => (string)$edge->to_node_id,
                'type' => $edge->edge_type ?? 'bezier', // Default to bezier for smooth connections
                'data' => [
                    'relationship_type' => $edge->relationship_type,
                    'edge_id' => $edge->id,
                    'user_id' => $edge->user_id
                ]
            ];
        });

        return response()->json([
            'nodes' => $nodes,
            'edges' => $vueFlowEdges,
        ]);
    }

    /**
     * Create the central node for a user (their own profile)
     */
    private function createCentralNode($userId, $user): void
    {
        // Check if central node already exists
        $existingCentralNode = FamilyTreeNode::where('user_id', $userId)
            ->where('profile_id', $userId)
            ->first();

        if (!$existingCentralNode) {
            FamilyTreeNode::create([
                'user_id' => $userId,
                'profile_id' => $userId,
                'relation' => 'self',
                'x_position' => 400, // Center position
                'y_position' => 300, // Center position
            ]);
        }
    }

    /**
     * Add a new node to the family tree
     */
    public function addNode(Request $request, $userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|exists:users,id',
            'relation' => 'required|string|in:parent,child,spouse,sibling,grandparent,grandchild,aunt,uncle,niece,nephew,cousin,father_in_law,mother_in_law,son_in_law,daughter_in_law,brother_in_law,sister_in_law,step_parent,step_child,step_sibling,friend,colleague,neighbor,mentor,student,teacher,guardian,foster_parent,foster_child',
            'x_position' => 'required|integer',
            'y_position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if node already exists
        $existingNode = FamilyTreeNode::where('user_id', $userId)
            ->where('profile_id', $request->profile_id)
            ->first();

        if ($existingNode) {
            return response()->json(['error' => 'Profile already exists in tree'], 409);
        }

        $node = FamilyTreeNode::create([
            'user_id' => $userId,
            'profile_id' => $request->profile_id,
            'relation' => $request->relation,
            'x_position' => $request->x_position,
            'y_position' => $request->y_position,
        ]);

        return response()->json($node->load('profile'), 201);
    }

    /**
     * Update a node
     */
    public function updateNode(Request $request, $userId, $nodeId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $node = FamilyTreeNode::where('user_id', $userId)
            ->where('id', $nodeId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'relation' => 'sometimes|string|in:parent,child,spouse,sibling,grandparent,grandchild,aunt,uncle,niece,nephew,cousin,father_in_law,mother_in_law,son_in_law,daughter_in_law,brother_in_law,sister_in_law,step_parent,step_child,step_sibling,friend,colleague,neighbor,mentor,student,teacher,guardian,foster_parent,foster_child',
            'x_position' => 'sometimes|integer',
            'y_position' => 'sometimes|integer',
            'custom_data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $node->update($request->only(['relation', 'x_position', 'y_position', 'custom_data']));

        return response()->json($node->load('profile'));
    }

    /**
     * Delete a node
     */
    public function deleteNode($userId, $nodeId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $node = FamilyTreeNode::where('user_id', $userId)
            ->where('id', $nodeId)
            ->firstOrFail();

        // Delete associated edges first
        FamilyTreeEdge::where('user_id', $userId)
            ->where(function($query) use ($nodeId) {
                $query->where('from_node_id', $nodeId)
                      ->orWhere('to_node_id', $nodeId);
            })
            ->delete();

        $node->delete();

        return response()->json(['message' => 'Node deleted successfully']);
    }

    /**
     * Add an edge between nodes
     */
    public function addEdge(Request $request, $userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'from_node_id' => 'required|exists:family_tree_nodes,id',
            'to_node_id' => 'required|exists:family_tree_nodes,id',
            'relationship_type' => 'required|string|in:family,marriage,adoption',
            'edge_type' => 'nullable|string|in:default,straight,step,bezier,smoothed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify both nodes belong to the same user
        $fromNode = FamilyTreeNode::where('user_id', $userId)->where('id', $request->from_node_id)->first();
        $toNode = FamilyTreeNode::where('user_id', $userId)->where('id', $request->to_node_id)->first();

        if (!$fromNode || !$toNode) {
            return response()->json(['error' => 'Invalid nodes'], 422);
        }

        // Check if edge already exists
        $existingEdge = FamilyTreeEdge::where('user_id', $userId)
            ->where(function($query) use ($request) {
                $query->where('from_node_id', $request->from_node_id)
                      ->where('to_node_id', $request->to_node_id);
            })
            ->orWhere(function($query) use ($request) {
                $query->where('from_node_id', $request->to_node_id)
                      ->where('to_node_id', $request->from_node_id);
            })
            ->first();

        if ($existingEdge) {
            return response()->json(['error' => 'Edge already exists'], 409);
        }

        $edge = FamilyTreeEdge::create([
            'user_id' => $userId,
            'from_node_id' => $request->from_node_id,
            'to_node_id' => $request->to_node_id,
            'relationship_type' => $request->relationship_type,
            'edge_type' => $request->edge_type ?? 'bezier', // Default to bezier for smooth family tree connections
        ]);

        // Return edge in Vue Flow format for proper frontend integration
        return response()->json([
            'id' => 'e' . $edge->from_node_id . '-' . $edge->to_node_id,
            'source' => (string)$edge->from_node_id,
            'target' => (string)$edge->to_node_id,
            'type' => $edge->edge_type ?? 'bezier',
            'data' => [
                'relationship_type' => $edge->relationship_type,
                'edge_id' => $edge->id,
                'user_id' => $edge->user_id
            ]
        ], 201);
    }

    /**
     * Update an edge
     */
    public function updateEdge(Request $request, $userId, $edgeId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'relationship_type' => 'sometimes|string|in:family,marriage,adoption',
            'edge_type' => 'sometimes|string|in:default,straight,step,bezier,smoothed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $edge = FamilyTreeEdge::where('user_id', $userId)
            ->where('id', $edgeId)
            ->firstOrFail();

        $edge->update($request->only(['relationship_type', 'edge_type']));

        // Return updated edge in Vue Flow format
        return response()->json([
            'id' => 'e' . $edge->from_node_id . '-' . $edge->to_node_id,
            'source' => (string)$edge->from_node_id,
            'target' => (string)$edge->to_node_id,
            'type' => $edge->edge_type ?? 'bezier',
            'data' => [
                'relationship_type' => $edge->relationship_type,
                'edge_id' => $edge->id,
                'user_id' => $edge->user_id
            ]
        ]);
    }

    /**
     * Delete an edge
     */
    public function deleteEdge($userId, $edgeId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $edge = FamilyTreeEdge::where('user_id', $userId)
            ->where('id', $edgeId)
            ->firstOrFail();

        $edge->delete();

        return response()->json(['message' => 'Edge deleted successfully']);
    }

    /**
     * Save custom layout
     */
    public function saveLayout(Request $request, $userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'layout_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update existing layout or create new one
        $layout = FamilyTreeLayout::updateOrCreate(
            ['user_id' => $userId, 'name' => $request->name],
            [
                'type' => 'custom',
                'layout_data' => $request->layout_data,
                'is_default' => false,
            ]
        );

        return response()->json($layout, 201);
    }

    /**
     * Save complete family tree state
     */
    public function saveFamilyTree(Request $request, $userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nodes' => 'required|array',
            'nodes.*.id' => 'required|string',
            'nodes.*.profile_id' => 'required|integer',
            'nodes.*.relation' => 'required|string',
            'nodes.*.x_position' => 'required|integer',
            'nodes.*.y_position' => 'required|integer',
            'edges' => 'array',
            'edges.*.id' => 'required|string',
            'edges.*.from_node_id' => 'required|integer',
            'edges.*.to_node_id' => 'required|integer',
            'edges.*.relationship_type' => 'required|string',
            'edges.*.edge_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Update or create nodes
            foreach ($request->nodes as $nodeData) {
                FamilyTreeNode::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'profile_id' => $nodeData['profile_id']
                    ],
                    [
                        'relation' => $nodeData['relation'],
                        'x_position' => $nodeData['x_position'],
                        'y_position' => $nodeData['y_position']
                    ]
                );
            }

            // Clear existing edges and create new ones
            FamilyTreeEdge::where('user_id', $userId)->delete();
            
            foreach ($request->edges as $edgeData) {
                FamilyTreeEdge::create([
                    'user_id' => $userId,
                    'from_node_id' => $edgeData['from_node_id'],
                    'to_node_id' => $edgeData['to_node_id'],
                    'relationship_type' => $edgeData['relationship_type'],
                    'edge_type' => $edgeData['edge_type']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Family tree saved successfully',
                'nodes_count' => count($request->nodes),
                'edges_count' => count($request->edges)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to save family tree: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save family tree: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate auto layouts
     */
    public function generateLayouts($userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $nodes = FamilyTreeNode::where('user_id', $userId)->get();
        $edges = FamilyTreeEdge::where('user_id', $userId)->get();

        if ($nodes->isEmpty()) {
            return response()->json(['error' => 'No nodes to generate layout for'], 400);
        }

        $layouts = $this->treeLayoutService->generateAllLayouts($nodes, $edges);

        // Save generated layouts
        foreach ($layouts as $type => $layoutData) {
            FamilyTreeLayout::updateOrCreate(
                ['user_id' => $userId, 'type' => $type],
                [
                    'name' => ucfirst($type),
                    'layout_data' => $layoutData,
                    'is_default' => false,
                ]
            );
        }

        return response()->json(['layouts' => $layouts]);
    }

    /**
     * Search for profiles to add
     */
    public function searchProfiles(Request $request, $userId): JsonResponse
    {
        if (Auth::id() !== (int)$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->get('q');
        if (empty($query)) {
            return response()->json(['profiles' => []]);
        }

        $profiles = User::where('id', '!=', $userId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'username', 'profile_photo_path', 'date_of_birth', 'location', 'bio', 'profession', 'passion', 'mission', 'calling')
            ->limit(10)
            ->get();

        return response()->json(['profiles' => $profiles]);
    }

    /**
     * Create a new profile and add to tree
     */
    public function createProfileAndAdd(Request $request): JsonResponse
    {
        $userId = Auth::id();
        \Log::info('createProfileAndAdd called with userId: ' . $userId);
        \Log::info('Request data:', $request->all());
        \Log::info('Authenticated user ID: ' . $userId);
        
        if (!$userId) {
            \Log::warning('No authenticated user found');
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'relation' => 'required|string|in:parent,child,spouse,sibling,grandparent,grandchild,aunt,uncle,niece,nephew,cousin,father_in_law,mother_in_law,son_in_law,daughter_in_law,brother_in_law,sister_in_law,step_parent,step_child,step_sibling,friend,colleague,neighbor,mentor,student,teacher,guardian,foster_parent,foster_child',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'profession' => 'nullable|string|max:255',
            'passion' => 'nullable|string|max:255',
            'mission' => 'nullable|string',
            'calling' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'x_position' => 'required|integer',
            'y_position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Handle profile photo upload
            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $profilePhotoPath = $file->storeAs('profile-photos', $filename, 'public');
                \Log::info('Profile photo uploaded to: ' . $profilePhotoPath);
            }

            // Log the data being sent
            \Log::info('Creating user with data:', [
                'name' => $request->name,
                'username' => $request->username,
                'email' => 'temp_' . time() . '_' . Str::random(8) . '@familytree.local', // More unique temporary email
                'relation' => $request->relation,
                'x_position' => $request->x_position,
                'y_position' => $request->y_position,
            ]);

            // Validate that all required fields are present
            $userData = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => 'temp_' . time() . '_' . Str::random(8) . '@familytree.local', // More unique temporary email
                'password' => bcrypt(Str::random(16)), // Temporary password
                'is_public' => false,
                'date_of_birth' => $request->date_of_birth ?: null,
                'location' => $request->location ?: null,
                'bio' => $request->bio ?: null,
                'profession' => $request->profession ?: null,
                'passion' => $request->passion ?: null,
                'mission' => $request->mission ?: null,
                'calling' => $request->calling ?: null,
                'profile_photo_path' => $profilePhotoPath,
            ];

            \Log::info('User data prepared:', $userData);

            // Create new user profile
            try {
                $newUser = User::create($userData);
                \Log::info('User created successfully with ID: ' . $newUser->id);
            } catch (\Exception $e) {
                \Log::error('Failed to create user: ' . $e->getMessage());
                throw new \Exception('Failed to create user profile: ' . $e->getMessage());
            }

            // Add to family tree
            \Log::info('Creating family tree node with data:', [
                'user_id' => $userId,
                'profile_id' => $newUser->id,
                'relation' => $request->relation,
                'x_position' => $request->x_position,
                'y_position' => $request->y_position,
            ]);

            try {
                $node = FamilyTreeNode::create([
                    'user_id' => $userId,
                    'profile_id' => $newUser->id,
                    'relation' => $request->relation,
                    'x_position' => $request->x_position,
                    'y_position' => $request->y_position,
                ]);
                \Log::info('Family tree node created successfully with ID: ' . $node->id);
            } catch (\Exception $e) {
                \Log::error('Failed to create family tree node: ' . $e->getMessage());
                throw new \Exception('Failed to create family tree node: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'node' => $node->load('profile'),
                'message' => 'Profile created and added to tree successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create profile: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to create profile: ' . $e->getMessage()], 500);
        }
    }
}
