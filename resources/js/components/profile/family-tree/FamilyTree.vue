<template>
  <div class="w-full h-full bg-gray-50">
    <!-- Main Content -->
    <div>
      <!-- Vue Flow Container -->
      <div class="bg-white border border-gray-200 p-4 mb-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900">Family Tree</h2>
          <div class="flex items-center space-x-2">
            <!-- Mode Toggle -->
            <button
              @click="toggleMode"
              :class="[
                'px-3 py-1.5 text-sm rounded-md transition-colors',
                isBuilderMode 
                  ? 'bg-blue-100 text-blue-700 border border-blue-300' 
                  : 'bg-gray-100 text-gray-700 border border-gray-300'
              ]"
            >
              {{ isBuilderMode ? 'Builder Mode' : 'Viewer Mode' }}
            </button>
            
            <!-- Add Member -->
            <button
              @click="showAddMemberModal = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Add Member
            </button>
            
            <!-- Layout Controls -->
            <div class="flex items-center space-x-2">
              <button
                @click="generateLayout"
                :disabled="isGeneratingLayout"
                class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
              >
                {{ isGeneratingLayout ? 'Generating...' : 'Auto Layout' }}
              </button>
              
              <select 
                v-model="currentLayout" 
                @change="applyLayout"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-md"
              >
                <option value="custom">Custom Layout</option>
                <option value="vertical">Vertical Layout</option>
                <option value="horizontal">Horizontal Layout</option>
                <option value="circular">Circular Layout</option>
                <option value="hierarchical">Hierarchical Layout</option>
              </select>
            </div>
            
            <!-- Advanced Features -->
            <div class="flex items-center space-x-2">
              <button
                @click="takeScreenshot"
                :disabled="isTakingScreenshot"
                class="px-3 py-1.5 text-sm bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors disabled:opacity-50"
                title="Take Screenshot"
              >
                {{ isTakingScreenshot ? 'Capturing...' : '📸' }}
              </button>
              
              <button
                @click="togglePerformanceMode"
                :class="[
                  'px-3 py-1.5 text-sm rounded-md transition-colors',
                  performanceMode 
                    ? 'bg-red-100 text-red-700 border border-red-300' 
                    : 'bg-gray-100 text-gray-700 border border-gray-300'
                ]"
                title="Performance Mode"
              >
                {{ performanceMode ? '⚡' : '🐌' }}
              </button>
              
              <button
                @click="toggleHelperLines"
                :class="[
                  'px-3 py-1.5 text-sm rounded-md transition-colors',
                  showHelperLines 
                    ? 'bg-purple-100 text-purple-700 border border-purple-300' 
                    : 'bg-gray-100 text-gray-700 border border-gray-300'
                ]"
                title="Helper Lines"
              >
                {{ showHelperLines ? '📏' : '📐' }}
              </button>
            </div>
            
            <!-- Save Tree -->
            <button
              @click="saveTree"
              :disabled="isSaving"
              class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors disabled:opacity-50"
            >
              {{ isSaving ? 'Saving...' : 'Save Tree' }}
            </button>
          </div>
        </div>
        
        <!-- Search Bar -->
        <div class="mt-4">
          <div class="relative">
            <input
              v-model="searchQuery"
              @input="handleSearch"
              @focus="showSearchResults = true"
              type="text"
              placeholder="Search profiles to add to tree..."
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            
            <!-- Search Results Dropdown -->
            <div 
              v-if="showSearchResults && searchResults.length > 0"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
              <div
                v-for="result in searchResults"
                :key="result.id"
                @click="addSearchResultToTree(result)"
                class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0"
              >
                <div class="flex items-center space-x-3">
                  <img 
                    :src="result.profile_photo_url || '/default-avatar.svg'" 
                    :alt="result.name"
                    class="w-8 h-8 rounded-full object-cover"
                  />
                  <div>
                    <p class="font-medium text-gray-900">{{ result.name }}</p>
                    <p class="text-sm text-gray-500">@{{ result.username }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vue Flow Canvas -->
      <div class="bg-white border border-gray-200 p-4">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center h-96">
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading family tree...</p>
          </div>
        </div>
        
        <!-- VueFlow Container -->
        <div 
          v-else
          class="relative" 
          style="height: 600px;"
          ref="vueFlowContainer"
        >
          <VueFlow
            v-model="elements"
            :node-types="nodeTypes"
            :edge-types="edgeTypes"
            :default-viewport="{ zoom: 1 }"
            :min-zoom="0.1"
            :max-zoom="4"
            :pan-on-scroll="true"
            :pan-on-drag="true"
            :zoom-on-scroll="true"
            :nodes-draggable="isBuilderMode"
            :nodes-connectable="isBuilderMode"
            :elements-selectable="true"
            :fit-view-on-init="true"
            :connection-line-style="{ stroke: '#3b82f6', strokeWidth: 2 }"
            :connection-radius="20"
            :multi-selection-key="'Shift'"
            :selection-key="'Ctrl'"
            :delete-key="'Delete'"
            :select-nodes-on-drag="false"
            class="w-full h-full"
            @node-drag-stop="handleNodeDragStop"
            @node-click="handleNodeClick"
            @edge-click="handleEdgeClick"
            @pane-click="handlePaneClick"
            @connect="handleConnect"
            @selection-change="handleSelectionChange"
            @node-double-click="handleNodeDoubleClick"
            @edge-double-click="handleEdgeDoubleClick"
          >
            <!-- Background -->
            <Background :pattern-color="'#94a3b8'" :gap="20" />
            
            <!-- Controls -->
            <Controls class="bg-white border border-gray-300 rounded-lg shadow-lg" />
            
            <!-- MiniMap -->
            <MiniMap 
              :mask-color="'rgba(1, 1, 1, 0.1)'"
              :node-color="'#3b82f6'"
              :edge-color="'#6b7280'"
              class="bg-white border border-gray-300 rounded-lg shadow-lg"
            />
            
            <!-- Helper Lines (Performance Mode) -->
            <div v-if="showHelperLines && performanceMode" class="helper-lines">
              <!-- Vertical helper lines -->
              <div 
                v-for="node in elements.filter(el => el.type === 'familyNode')"
                :key="`v-${node.id}`"
                class="absolute w-px bg-blue-200 opacity-50"
                :style="{
                  left: `${node.position.x + 70}px`,
                  top: '0',
                  height: '100%'
                }"
              ></div>
              
              <!-- Horizontal helper lines -->
              <div 
                v-for="node in elements.filter(el => el.type === 'familyNode')"
                :key="`h-${node.id}`"
                class="absolute h-px bg-blue-200 opacity-50"
                :style="{
                  top: `${node.position.y + 70}px`,
                  left: '0',
                  width: '100%'
                }"
              ></div>
            </div>
          </VueFlow>
        </div>
      </div>

      <!-- Selection Actions -->
      <div v-if="selectedElements.length > 0" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
        <div class="flex items-center justify-between">
          <p class="text-blue-800">
            {{ selectedElements.length }} element{{ selectedElements.length > 1 ? 's' : '' }} selected
          </p>
          <div class="flex items-center space-x-2">
            <button
              @click="deleteSelectedElements"
              class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              Delete Selected
            </button>
            <button
              @click="alignSelectedElements"
              class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
            >
              Align Selected
            </button>
            <button
              @click="clearSelection"
              class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
            >
              Clear Selection
            </button>
          </div>
        </div>
      </div>

      <!-- Add Member Modal -->
      <AddMemberModal
        v-if="showAddMemberModal"
        :profile-user-data="profileUserData"
        @close="showAddMemberModal = false"
        @add-person="addPersonToTree"
      />

      <!-- Profile Popup Modal -->
      <ProfilePopupModal
        v-if="showProfilePopup"
        :profile="selectedProfile"
        @close="showProfilePopup = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { VueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import FamilyTreeNode from './FamilyTreeNode.vue';
import AddMemberModal from './AddMemberModal.vue';
import ProfilePopupModal from './ProfilePopupModal.vue';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  },
  isEditable: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['tree-updated']);

// Component state
const elements = ref([]);
const isBuilderMode = ref(props.isEditable);
const showAddMemberModal = ref(false);
const showProfilePopup = ref(false);
const selectedProfile = ref(null);
const isGeneratingLayout = ref(false);
const isSaving = ref(false);
const isLoading = ref(true);
const hasLoadedTree = ref(false);

// Advanced features state
const currentLayout = ref('custom');
const performanceMode = ref(false);
const showHelperLines = ref(false);
const isTakingScreenshot = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const showSearchResults = ref(false);
const selectedElements = ref([]);
const vueFlowContainer = ref(null);

// Vue Flow node types
const nodeTypes = {
  familyNode: FamilyTreeNode
};

// Vue Flow edge types
const edgeTypes = {
  default: 'default'
};

// Methods
const toggleMode = () => {
  isBuilderMode.value = !isBuilderMode.value;
};

// Load existing tree data
const loadTreeData = async () => {
  if (hasLoadedTree.value) return;
  
  try {
    isLoading.value = true;
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    if (response.ok) {
      const treeData = await response.json();
      console.log('Loaded tree data:', treeData);
      
      // Convert backend data to VueFlow format
      const vueFlowNodes = treeData.nodes.map(node => ({
        id: node.id.toString(),
        type: 'familyNode',
        position: { x: node.x_position, y: node.y_position },
        data: {
          id: node.id,
          name: node.profile?.name || 'Unknown',
          username: node.profile?.username || 'No username',
          profile_photo_url: node.profile?.profile_photo_path ? `/storage/${node.profile.profile_photo_path}` : null,
          relation: node.relation,
          showDetails: !performanceMode.value,
          // Add additional profile data
          date_of_birth: node.profile?.date_of_birth,
          location: node.profile?.location,
          bio: node.profile?.bio,
          profession: node.profile?.profession,
          passion: node.profile?.passion,
          mission: node.profile?.mission,
          calling: node.profile?.calling
        }
      }));
      
      const vueFlowEdges = treeData.edges || [];
      
      // Combine nodes and edges
      elements.value = [...vueFlowNodes, ...vueFlowEdges];
      hasLoadedTree.value = true;
      
      console.log('VueFlow elements set:', elements.value);
    }
  } catch (error) {
    console.error('Failed to load tree data:', error);
  } finally {
    isLoading.value = false;
  }
};

const togglePerformanceMode = () => {
  performanceMode.value = !performanceMode.value;
  // Update node details visibility based on performance mode
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode') {
      return {
        ...el,
        data: {
          ...el.data,
          showDetails: !performanceMode.value
        }
      };
    }
    return el;
  });
};

const toggleHelperLines = () => {
  showHelperLines.value = !showHelperLines.value;
};

const addPersonToTree = async (person) => {
  console.log('Adding person to tree:', person);
  
  // Handle both person objects and treeNode objects from the modal
  const personData = person.node ? person.node : person;
  const profileData = personData.profile ? personData.profile : personData;
  
  const newNode = {
    id: profileData.id.toString(),
    type: 'familyNode',
    position: { 
      x: personData.x_position || personData.x || Math.random() * 200 - 100, 
      y: personData.y_position || personData.y || Math.random() * 200 - 100 
    },
    data: {
      id: profileData.id,
      name: profileData.name,
      username: profileData.username,
      profile_photo_url: profileData.profile_photo_url || profileData.profile_photo_path ? `/storage/${profileData.profile_photo_path}` : null,
      relation: personData.relation || 'family',
      showDetails: !performanceMode.value,
      // Add additional profile data
      date_of_birth: profileData.date_of_birth,
      location: profileData.location,
      bio: profileData.bio,
      profession: profileData.profession,
      passion: profileData.passion,
      mission: profileData.mission,
      calling: profileData.calling
    }
  };
  
  console.log('Created new node:', newNode);
  elements.value = [...elements.value, newNode];
  emit('tree-updated');
};

// Search functionality
const handleSearch = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }
  
  try {
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/search?q=${encodeURIComponent(searchQuery.value)}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      searchResults.value = data.profiles || [];
    }
  } catch (error) {
    console.error('Search error:', error);
  }
};

const addSearchResultToTree = (profile) => {
  // Check if profile already exists in tree
  const exists = elements.value.some(el => 
    el.type === 'familyNode' && el.data.id === profile.id
  );
  
  if (exists) {
    alert('This profile is already in the tree!');
    return;
  }
  
  const newNode = {
    id: profile.id.toString(),
    type: 'familyNode',
    position: { x: Math.random() * 200 - 100, y: Math.random() * 200 - 100 },
    data: {
      id: profile.id,
      name: profile.name,
      username: profile.username,
      profile_photo_url: profile.profile_photo_url,
      relation: 'family',
      showDetails: !performanceMode.value
    }
  };
  
  elements.value = [...elements.value, newNode];
  searchQuery.value = '';
  showSearchResults.value = false;
  searchResults.value = [];
  emit('tree-updated');
};

// Screenshot functionality
const takeScreenshot = async () => {
  isTakingScreenshot.value = true;
  
  try {
    // Import html2canvas dynamically
    const html2canvas = (await import('html2canvas')).default;
    
    if (vueFlowContainer.value) {
      const canvas = await html2canvas(vueFlowContainer.value, {
        backgroundColor: '#ffffff',
        scale: 2,
        useCORS: true,
        allowTaint: true
      });
      
      // Create download link
      const link = document.createElement('a');
      link.download = `family-tree-${Date.now()}.png`;
      link.href = canvas.toDataURL();
      link.click();
    }
  } catch (error) {
    console.error('Screenshot error:', error);
  } finally {
    isTakingScreenshot.value = false;
  }
};

// Multi-selection functionality
const handleSelectionChange = (event) => {
  selectedElements.value = event.elements || [];
};

const deleteSelectedElements = () => {
  if (confirm(`Are you sure you want to delete ${selectedElements.value.length} selected element(s)?`)) {
    const selectedIds = selectedElements.value.map(el => el.id);
    elements.value = elements.value.filter(el => !selectedIds.includes(el.id));
    selectedElements.value = [];
  }
};

const alignSelectedElements = () => {
  if (selectedElements.value.length < 2) return;
  
  const nodes = selectedElements.value.filter(el => el.type === 'familyNode');
  if (nodes.length < 2) return;
  
  // Align horizontally (same Y position)
  const avgY = nodes.reduce((sum, node) => sum + node.position.y, 0) / nodes.length;
  
  elements.value = elements.value.map(el => {
    if (selectedElements.value.some(selected => selected.id === el.id)) {
      return { ...el, position: { ...el.position, y: avgY } };
    }
    return el;
  });
};

const clearSelection = () => {
  selectedElements.value = [];
};

// Layout algorithms
const applyLayout = () => {
  switch (currentLayout.value) {
    case 'vertical':
      applyVerticalLayout();
      break;
    case 'horizontal':
      applyHorizontalLayout();
      break;
    case 'circular':
      applyCircularLayout();
      break;
    case 'hierarchical':
      applyHierarchicalLayout();
      break;
    default:
      // Custom layout - do nothing
      break;
  }
};

const applyVerticalLayout = () => {
  const nodes = elements.value.filter(el => el.type === 'familyNode');
  const centerNode = nodes.find(node => node.data.relation === 'self');
  
  if (!centerNode) return;
  
  const otherNodes = nodes.filter(node => node.id !== centerNode.id);
  const spacing = 200;
  
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode' && el.id !== centerNode.id) {
      const index = otherNodes.findIndex(node => node.id === el.id);
      const x = centerNode.position.x + (index % 3 - 1) * spacing;
      const y = centerNode.position.y + Math.floor(index / 3) * spacing;
      return { ...el, position: { x, y } };
    }
    return el;
  });
};

const applyHorizontalLayout = () => {
  const nodes = elements.value.filter(el => el.type === 'familyNode');
  const centerNode = nodes.find(node => node.data.relation === 'self');
  
  if (!centerNode) return;
  
  const otherNodes = nodes.filter(node => node.id !== centerNode.id);
  const spacing = 200;
  
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode' && el.id !== centerNode.id) {
      const index = otherNodes.findIndex(node => node.id === el.id);
      const x = centerNode.position.x + index * spacing;
      const y = centerNode.position.y;
      return { ...el, position: { x, y } };
    }
    return el;
  });
};

const applyCircularLayout = () => {
  const nodes = elements.value.filter(el => el.type === 'familyNode');
  const centerNode = nodes.find(node => node.data.relation === 'self');
  
  if (!centerNode) return;
  
  const otherNodes = nodes.filter(node => node.id !== centerNode.id);
  const radius = 200;
  
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode' && el.id !== centerNode.id) {
      const index = otherNodes.findIndex(node => node.id === el.id);
      const angle = (index / otherNodes.length) * 2 * Math.PI;
      const x = centerNode.position.x + Math.cos(angle) * radius;
      const y = centerNode.position.y + Math.sin(angle) * radius;
      return { ...el, position: { x, y } };
    }
    return el;
  });
};

const applyHierarchicalLayout = () => {
  const nodes = elements.value.filter(el => el.type === 'familyNode');
  const centerNode = nodes.find(node => node.data.relation === 'self');
  
  if (!centerNode) return;
  
  // Group nodes by relation type
  const relationGroups = {
    'parent': [],
    'child': [],
    'spouse': [],
    'sibling': [],
    'other': []
  };
  
  nodes.forEach(node => {
    if (node.id === centerNode.id) return;
    
    if (relationGroups[node.data.relation]) {
      relationGroups[node.data.relation].push(node);
    } else {
      relationGroups.other.push(node);
    }
  });
  
  let currentY = centerNode.position.y - 300;
  const spacing = 150;
  
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode' && el.id !== centerNode.id) {
      let group = 'other';
      for (const [key, nodes] of Object.entries(relationGroups)) {
        if (nodes.some(n => n.id === el.id)) {
          group = key;
          break;
        }
      }
      
      const groupNodes = relationGroups[group];
      const index = groupNodes.findIndex(node => node.id === el.id);
      const x = centerNode.position.x + (index - (groupNodes.length - 1) / 2) * spacing;
      const y = currentY;
      
      if (index === groupNodes.length - 1) {
        currentY += spacing;
      }
      
      return { ...el, position: { x, y } };
    }
    return el;
  });
};

// Vue Flow event handlers
const handleConnect = (params) => {
  // Validate connection
  if (params.source === params.target) {
    alert('Cannot connect a node to itself!');
    return;
  }
  
  // Check if connection already exists
  const existingEdge = elements.value.find(el => 
    el.type === 'default' && 
    el.source === params.source && 
    el.target === params.target
  );
  
  if (existingEdge) {
    alert('Connection already exists!');
    return;
  }
  
  const newEdge = {
    id: `e${params.source}-${params.target}`,
    source: params.source,
    target: params.target,
    type: 'default',
    data: { relationship_type: 'family' }
  };
  elements.value = [...elements.value, newEdge];
};

const handleNodeDragStop = (event, node) => {
  const updatedElements = elements.value.map(el => {
    if (el.id === node.id) {
      return { ...el, position: node.position };
    }
    return el;
  });
  elements.value = updatedElements;
};

const handleNodeClick = (event, node) => {
  selectedProfile.value = node.data;
  showProfilePopup.value = true;
};

const handleNodeDoubleClick = (event, node) => {
  // Enter edit mode for node
  console.log('Edit node:', node);
};

const handleEdgeClick = (event, edge) => {
  const newRelation = prompt('Enter relationship type:', edge.data.relationship_type || 'family');
  if (newRelation) {
    const updatedElements = elements.value.map(el => {
      if (el.id === edge.id) {
        return { ...el, data: { ...el.data, relationship_type: newRelation } };
      }
      return el;
    });
    elements.value = updatedElements;
  }
};

const handleEdgeDoubleClick = (event, edge) => {
  // Enter edit mode for edge
  console.log('Edit edge:', edge);
};

const handlePaneClick = (event) => {
  // Clear selection on background click
  selectedElements.value = [];
  showSearchResults.value = false;
};

// Layout generation based on math.html example
const generateLayout = () => {
  isGeneratingLayout.value = true;
  
  setTimeout(() => {
    const nodes = elements.value.filter(el => el.type === 'familyNode');
    const centerNode = nodes.find(node => node.data.relation === 'self');
    
    if (centerNode && nodes.length > 1) {
      const updatedElements = elements.value.map(el => {
        if (el.type === 'familyNode' && el.id !== centerNode.id) {
          // Calculate position based on relation and available space
          const angle = Math.random() * 2 * Math.PI;
          const distance = 150 + Math.random() * 100;
          const x = centerNode.position.x + Math.cos(angle) * distance;
          const y = centerNode.position.y + Math.sin(angle) * distance;
          
          return { ...el, position: { x, y } };
        }
        return el;
      });
      
      elements.value = updatedElements;
    }
    
    isGeneratingLayout.value = false;
  }, 500);
};

// Save tree functionality based on save.html example
const saveTree = async () => {
  isSaving.value = true;
  
  try {
    const nodes = elements.value.filter(el => el.type === 'familyNode');
    const edges = elements.value.filter(el => el.type === 'default');
    
    const treeData = {
      nodes: nodes.map(node => ({
        profile_id: node.data.id,
        relation: node.data.relation,
        x_position: Math.round(node.position.x),
        y_position: Math.round(node.position.y)
      })),
      edges: edges.map(edge => ({
        from_node_id: edge.source,
        to_node_id: edge.target,
        relationship_type: edge.data.relationship_type || 'family',
        edge_type: 'bezier'
      }))
    };
    
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/save`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify(treeData)
    });
    
    if (response.ok) {
      console.log('Tree saved successfully');
    } else {
      console.error('Failed to save tree');
    }
  } catch (error) {
    console.error('Error saving tree:', error);
  } finally {
    isSaving.value = false;
  }
};

// Keyboard shortcuts
const handleKeyDown = (event) => {
  if (event.key === 'Delete' && selectedElements.value.length > 0) {
    deleteSelectedElements();
  }
  
  if (event.ctrlKey && event.key === 'a') {
    event.preventDefault();
    selectedElements.value = elements.value;
  }
  
  if (event.ctrlKey && event.key === 's') {
    event.preventDefault();
    saveTree();
  }
};

onMounted(async () => {
  console.log('FamilyTree mounted with props:', props);
  
  // Set initial builder mode
  isBuilderMode.value = props.isEditable;
  
  // Create center node with improved positioning
  if (props.profileUserData && props.profileUserData.id) {
    // Calculate center position based on container size
    const containerWidth = 800; // Approximate container width
    const containerHeight = 600; // Approximate container height
    const centerX = containerWidth / 2;
    const centerY = containerHeight / 2;
    
    const centerNode = {
      id: props.profileUserData.id.toString(),
      type: 'familyNode',
      position: { x: centerX, y: centerY },
      data: {
        id: props.profileUserData.id,
        name: props.profileUserData.name,
        username: props.profileUserData.username,
        profile_photo_url: props.profileUserData.profile_photo_url,
        relation: 'self',
        showDetails: !performanceMode.value,
        // Add additional profile data
        date_of_birth: props.profileUserData.date_of_birth,
        location: props.profileUserData.location,
        bio: props.profileUserData.bio,
        profession: props.profileUserData.profession,
        passion: props.profileUserData.passion,
        mission: props.profileUserData.mission,
        calling: props.profileUserData.calling
      }
    };
    
    console.log('Creating center node:', centerNode);
    elements.value = [centerNode];
    console.log('Elements after center node creation:', elements.value);
  } else {
    console.error('No profile user data available:', props.profileUserData);
  }
  
  // Load existing tree data
  await loadTreeData();
  
  // Add keyboard event listener
  document.addEventListener('keydown', handleKeyDown);
});

// Cleanup
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

// Watch elements for changes
watch(elements, (newElements) => {
  // Auto-save functionality could be added here
}, { deep: true });

// Watch search query for debouncing
let searchTimeout;
watch(searchQuery, (newQuery) => {
  clearTimeout(searchTimeout);
  if (newQuery.length >= 2) {
    searchTimeout = setTimeout(() => {
      handleSearch();
    }, 300);
  } else {
    searchResults.value = [];
  }
});
</script>

<style scoped>
/* Custom styles for the family tree */
</style>
