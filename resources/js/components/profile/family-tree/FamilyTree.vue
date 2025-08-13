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
            @edge-double-click="handleEdgeDoubleClick"
            @edge-context-menu="handleEdgeContextMenu"
            @pane-click="handlePaneClick"
            @connect="handleConnect"
            @selection-change="handleSelectionChange"
            @node-double-click="handleNodeDoubleClick"
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

      <!-- Error Popup -->
      <ErrorPopup
        v-if="showErrorPopup"
        :message="errorMessage"
        @close="showErrorPopup = false"
      />

      <!-- Success Popup -->
      <SuccessPopup
        v-if="showSuccessPopup"
        :message="successMessage"
        @close="showSuccessPopup = false"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { VueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import FamilyTreeNode from './FamilyTreeNode.vue';
import AddMemberModal from './AddMemberModal.vue';
import ProfilePopupModal from './ProfilePopupModal.vue';
import ErrorPopup from '../../ErrorPopup.vue';
import SuccessPopup from '../../SuccessPopup.vue';
import { initializeApiVerification } from '../../../utils/apiVerification';

// Types
interface ProfileUserData {
  id: number;
  name: string;
  username: string;
  profile_photo_url?: string;
  date_of_birth?: string;
  location?: string;
  bio?: string;
  profession?: string;
  passion?: string;
  mission?: string;
  calling?: string;
}

interface TreeNode {
  id: string;
  type: string;
  position: { x: number; y: number };
  data: {
    id: number;
    name: string;
    username: string;
    profile_photo_url?: string;
    relation: string;
    showDetails: boolean;
    date_of_birth?: string;
    location?: string;
    bio?: string;
    profession?: string;
    passion?: string;
    mission?: string;
    calling?: string;
  };
}

interface TreeEdge {
  id: string;
  source: string;
  target: string;
  type: string;
  data: { relationship_type: string };
}

interface SearchResult {
  id: number;
  name: string;
  username: string;
  profile_photo_url?: string;
}

interface TreeData {
  nodes: Array<{
    id: number;
    profile?: ProfileUserData;
    relation: string;
    x_position: number;
    y_position: number;
  }>;
  edges?: TreeEdge[];
}

const props = defineProps<{
  profileUserData: ProfileUserData;
  isEditable: boolean;
}>();

const emit = defineEmits<{
  'tree-updated': [];
}>();

// Component state
const elements = ref<(TreeNode | TreeEdge)[]>([]);
const isBuilderMode = ref(props.isEditable);
const showAddMemberModal = ref(false);
const showProfilePopup = ref(false);
const selectedProfile = ref<ProfileUserData | null>(null);
const isGeneratingLayout = ref(false);
const isSaving = ref(false);
const isLoading = ref(true);
const hasLoadedTree = ref(false);

// Popup state
const showErrorPopup = ref(false);
const showSuccessPopup = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Advanced features state
const currentLayout = ref<'custom' | 'vertical' | 'horizontal' | 'circular' | 'hierarchical'>('custom');
const performanceMode = ref(false);
const showHelperLines = ref(false);
const isTakingScreenshot = ref(false);
const searchQuery = ref('');
const searchResults = ref<SearchResult[]>([]);
const showSearchResults = ref(false);
const selectedElements = ref<(TreeNode | TreeEdge)[]>([]);
const vueFlowContainer = ref<HTMLElement | null>(null);

// Vue Flow node types
const nodeTypes = {
  familyNode: FamilyTreeNode
};

// Vue Flow edge types
const edgeTypes = {
  default: 'default'
};

// Methods
const toggleMode = (): void => {
  isBuilderMode.value = !isBuilderMode.value;
};

// Load existing tree data
const loadTreeData = async (): Promise<void> => {
  if (hasLoadedTree.value) return;
  
  try {
    isLoading.value = true;
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      const treeData: TreeData = await response.json();
      
      // Convert backend data to VueFlow format
      const vueFlowNodes: TreeNode[] = treeData.nodes.map(node => ({
        id: node.id.toString(),
        type: 'familyNode',
        position: { x: node.x_position, y: node.y_position },
        data: {
          id: node.id,
          name: node.profile?.name || 'Unknown',
          username: node.profile?.username || 'No username',
          profile_photo_url: node.profile?.profile_photo_path ? `/storage/${node.profile.profile_photo_path}` : undefined,
          relation: node.relation,
          showDetails: !performanceMode.value,
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
    } else {
      showErrorMessage('Failed to load family tree data. Please refresh the page and try again.');
    }
  } catch (error) {
    showErrorMessage('Failed to load family tree. Please check your connection and try again.');
  } finally {
    isLoading.value = false;
  }
};

const togglePerformanceMode = (): void => {
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

const toggleHelperLines = (): void => {
  showHelperLines.value = !showHelperLines.value;
};

const addPersonToTree = async (person: any): Promise<void> => {
  try {
    // Handle both person objects and treeNode objects from the modal
    const personData = person.node ? person.node : person;
    const profileData = personData.profile ? personData.profile : personData;
    
    // Find center node for positioning
    const centerNode = elements.value.find(el => 
      el.type === 'familyNode' && (el as TreeNode).data.relation === 'self'
    ) as TreeNode;

    if (!centerNode) {
      showErrorMessage('Center node not found. Please refresh the page.');
      return;
    }

    // Calculate intelligent position relative to center node
    const spacing = 200;
    const existingNodes = elements.value.filter(el => 
      el.type === 'familyNode' && (el as TreeNode).data.relation !== 'self'
    );
    
    let newX = centerNode.position.x + spacing;
    let newY = centerNode.position.y;
    
    // Avoid overlapping with existing nodes
    while (existingNodes.some(node => 
      Math.abs((node as TreeNode).position.x - newX) < 100 && 
      Math.abs((node as TreeNode).position.y - newY) < 100
    )) {
      newX += spacing;
      if (newX > centerNode.position.x + 600) {
        newX = centerNode.position.x - spacing;
        newY += spacing;
      }
    }

    const newNode: TreeNode = {
      id: profileData.id.toString(),
      type: 'familyNode',
      position: { 
        x: newX, 
        y: newY 
      },
      data: {
        id: profileData.id,
        name: profileData.name,
        username: profileData.username,
        profile_photo_url: profileData.profile_photo_url || profileData.profile_photo_path ? `/storage/${profileData.profile_photo_path}` : undefined,
        relation: personData.relation || 'friend',
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

    // Add to tree
    elements.value = [...elements.value, newNode];
    
    // Save to backend if not already saved
    if (!personData.node_id) {
      try {
        const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/node`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            profile_id: profileData.id,
            relation: personData.relation || 'friend',
            x_position: Math.round(newX),
            y_position: Math.round(newY)
          })
        });

        if (response.ok) {
          const savedNode = await response.json();
          
          // Update node with backend data
          const updatedElements = elements.value.map(el => {
            if (el.id === newNode.id) {
              return {
                ...el,
                data: {
                  ...el.data,
                  node_id: savedNode.id
                }
              };
            }
            return el;
          });
          elements.value = updatedElements;
        } else {
          throw new Error('Failed to save node to backend');
        }
      } catch (error) {
        showErrorMessage('Profile added to tree but failed to save. Please try saving the tree manually.');
      }
    }
    
    emit('tree-updated');
    showSuccessMessage('Person added to tree successfully!');
  } catch (error) {
    showErrorMessage('Failed to add person to tree. Please try again.');
  }
};

// Search functionality
const handleSearch = async (): Promise<void> => {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  try {
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/search?q=${encodeURIComponent(searchQuery.value)}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      // Filter out profiles that are already in the tree
      const existingProfileIds = elements.value
        .filter(el => el.type === 'familyNode')
        .map(el => (el as TreeNode).data.id);
      
      searchResults.value = data.profiles.filter((profile: any) => 
        !existingProfileIds.includes(profile.id)
      );
    } else {
      showErrorMessage('Search failed. Please try again.');
      searchResults.value = [];
    }
  } catch (error) {
    showErrorMessage('Search error. Please try again.');
    searchResults.value = [];
  }
};

const addSearchResultToTree = async (profile: any): Promise<void> => {
  try {
    // Find center node for positioning
    const centerNode = elements.value.find(el => 
      el.type === 'familyNode' && (el as TreeNode).data.relation === 'self'
    ) as TreeNode;

    if (!centerNode) {
      showErrorMessage('Center node not found. Please refresh the page.');
      return;
    }

    // Calculate position relative to center node
    const spacing = 200;
    const existingNodes = elements.value.filter(el => 
      el.type === 'familyNode' && (el as TreeNode).data.relation !== 'self'
    );
    
    let newX = centerNode.position.x + spacing;
    let newY = centerNode.position.y;
    
    // Avoid overlapping with existing nodes
    while (existingNodes.some(node => 
      Math.abs((node as TreeNode).position.x - newX) < 100 && 
      Math.abs((node as TreeNode).position.y - newY) < 100
    )) {
      newX += spacing;
      if (newX > centerNode.position.x + 600) {
        newX = centerNode.position.x - spacing;
        newY += spacing;
      }
    }

    // Create new node
    const newNode: TreeNode = {
      id: profile.id.toString(),
      type: 'familyNode',
      position: { x: newX, y: newY },
      data: {
        id: profile.id,
        name: profile.name,
        username: profile.username,
        profile_photo_url: profile.profile_photo_path ? `/storage/${profile.profile_photo_path}` : undefined,
        relation: 'friend', // Default relation, can be changed later
        showDetails: !performanceMode.value,
        date_of_birth: profile.date_of_birth,
        location: profile.location,
        bio: profile.bio,
        profession: profile.profession,
        passion: profile.passion,
        mission: profile.mission,
        calling: profile.calling
      }
    };

    // Add to tree
    elements.value = [...elements.value, newNode];
    
    // Clear search
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    
    // Save to backend
    try {
      const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/node`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          profile_id: profile.id,
          relation: 'friend',
          x_position: Math.round(newX),
          y_position: Math.round(newY)
        })
      });

      if (response.ok) {
        const savedNode = await response.json();
        
        // Update node with backend data
        const updatedElements = elements.value.map(el => {
          if (el.id === newNode.id) {
            return {
              ...el,
              data: {
                ...el.data,
                node_id: savedNode.id
              }
            };
          }
          return el;
        });
        elements.value = updatedElements;
        
        emit('tree-updated');
        showSuccessMessage('Profile added to tree successfully!');
      } else {
        throw new Error('Failed to save node to backend');
      }
    } catch (error) {
      showErrorMessage('Profile added to tree but failed to save. Please try saving the tree manually.');
    }
  } catch (error) {
    showErrorMessage('Failed to add profile to tree. Please try again.');
  }
};

// Screenshot functionality
const takeScreenshot = async (): Promise<void> => {
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
    showErrorMessage('Failed to take screenshot. Please try again.');
  } finally {
    isTakingScreenshot.value = false;
  }
};

// Multi-selection functionality
const handleSelectionChange = (event: any): void => {
  selectedElements.value = event.elements || [];
};

const deleteSelectedElements = (): void => {
  if (confirm(`Are you sure you want to delete ${selectedElements.value.length} selected element(s)?`)) {
    try {
      const selectedIds = selectedElements.value.map(el => el.id);
      elements.value = elements.value.filter(el => !selectedIds.includes(el.id));
      selectedElements.value = [];
      emit('tree-updated');
      showSuccessMessage('Selected elements deleted successfully!');
    } catch (error) {
      showErrorMessage('Failed to delete elements. Please try again.');
    }
  }
};

const alignSelectedElements = (): void => {
  if (selectedElements.value.length < 2) return;
  
  try {
    const nodes = selectedElements.value.filter(el => el.type === 'familyNode') as TreeNode[];
    if (nodes.length < 2) return;
    
    // Align horizontally (same Y position)
    const avgY = nodes.reduce((sum, node) => sum + node.position.y, 0) / nodes.length;
    
    elements.value = elements.value.map(el => {
      if (selectedElements.value.some(selected => selected.id === el.id)) {
        return { ...el, position: { ...el.position, y: avgY } };
      }
      return el;
    });
    showSuccessMessage('Selected elements aligned successfully!');
  } catch (error) {
    showErrorMessage('Failed to align elements. Please try again.');
  }
};

const clearSelection = (): void => {
  selectedElements.value = [];
};

// Layout algorithms
const applyLayout = (): void => {
  try {
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
  } catch (error) {
    showErrorMessage('Failed to apply layout. Please try again.');
  }
};

const applyVerticalLayout = (): void => {
  const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
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

const applyHorizontalLayout = (): void => {
  const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
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

const applyCircularLayout = (): void => {
  const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
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

const applyHierarchicalLayout = (): void => {
  const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
  const centerNode = nodes.find(node => node.data.relation === 'self');
  
  if (!centerNode) return;
  
  // Group nodes by relation type
  const relationGroups: Record<string, TreeNode[]> = {
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
const handleConnect = async (params: any): Promise<void> => {
  try {
    // Validate connection
    if (params.source === params.target) {
      showErrorMessage('Cannot connect a node to itself!');
      return;
    }
    
    // Check if connection already exists
    const existingEdge = elements.value.find(el => 
      el.type === 'default' && 
      (el as TreeEdge).source === params.source && 
      (el as TreeEdge).target === params.target
    );
    
    if (existingEdge) {
      showErrorMessage('Connection already exists!');
      return;
    }

    // Create new edge in Vue Flow format
    const newEdge: TreeEdge = {
      id: `e${params.source}-${params.target}`,
      source: params.source,
      target: params.target,
      type: 'default',
      data: { relationship_type: 'family' }
    };

    // Add to local elements immediately for visual feedback
    elements.value = [...elements.value, newEdge];

    // Save edge to backend
    try {
      const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/edge`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          from_node_id: params.source,
          to_node_id: params.target,
          relationship_type: 'family',
          edge_type: 'bezier'
        })
      });

      if (response.ok) {
        const savedEdge = await response.json();
        
        // Update the edge with backend data
        const updatedElements = elements.value.map(el => {
          if (el.id === newEdge.id) {
            return {
              ...el,
              data: {
                ...el.data,
                edge_id: savedEdge.data.edge_id,
                user_id: savedEdge.data.user_id
              }
            };
          }
          return el;
        });
        elements.value = updatedElements;
        
        emit('tree-updated');
        showSuccessMessage('Connection created successfully!');
      } else {
        // Remove the edge if backend save failed
        elements.value = elements.value.filter(el => el.id !== newEdge.id);
        throw new Error('Failed to save edge to backend');
      }
    } catch (error) {
      showErrorMessage('Failed to create connection. Please try again.');
      // Remove the edge from local elements
      elements.value = elements.value.filter(el => el.id !== newEdge.id);
    }
  } catch (error) {
    showErrorMessage('Failed to create connection. Please try again.');
  }
};

const handleNodeDragStop = async (event: any, node: TreeNode): Promise<void> => {
  try {
    // Update local elements immediately for visual feedback
    const updatedElements = elements.value.map(el => {
      if (el.id === node.id) {
        return { ...el, position: node.position };
      }
      return el;
    });
    elements.value = updatedElements;

    // Save node position to backend
    try {
      const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/node/${node.id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          x_position: Math.round(node.position.x),
          y_position: Math.round(node.position.y)
        })
      });

      if (!response.ok) {
        showErrorMessage('Failed to save node position. Please try saving the tree manually.');
      }
    } catch (error) {
      showErrorMessage('Failed to save node position. Please try saving the tree manually.');
    }

    emit('tree-updated');
  } catch (error) {
    showErrorMessage('Failed to update node position. Please try again.');
  }
};

const handleNodeClick = (event: any, node: TreeNode): void => {
  try {
    selectedProfile.value = node.data;
    showProfilePopup.value = true;
  } catch (error) {
    showErrorMessage('Failed to open profile. Please try again.');
  }
};

const handleNodeDoubleClick = (event: any, node: TreeNode): void => {
  // Enter edit mode for node - future enhancement
  showSuccessMessage('Double-click functionality coming soon!');
};

const handleEdgeClick = async (event: any, edge: TreeEdge): Promise<void> => {
  try {
    const newRelation = prompt('Enter relationship type:', edge.data.relationship_type || 'family');
    if (newRelation) {
      // Update local edge immediately
      const updatedElements = elements.value.map(el => {
        if (el.id === edge.id) {
          return { ...el, data: { ...el.data, relationship_type: newRelation } };
        }
        return el;
      });
      elements.value = updatedElements;

      // Save to backend
      try {
        const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/edge/${edge.data.edge_id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            relationship_type: newRelation
          })
        });

        if (response.ok) {
          elements.value = updatedElements;
          emit('tree-updated');
          showSuccessMessage('Relationship updated successfully!');
        } else {
          throw new Error('Failed to update edge');
        }
      } catch (error) {
        showErrorMessage('Edge updated locally but failed to save. Please try saving the tree manually.');
      }
    }
  } catch (error) {
    showErrorMessage('Failed to update relationship. Please try again.');
  }
};

const handleEdgeDoubleClick = async (event: any, edge: TreeEdge): Promise<void> => {
  try {
    const newRelation = prompt('Enter relationship type:', edge.data.relationship_type || 'family');
    if (newRelation) {
      // Update local edge immediately
      const updatedElements = elements.value.map(el => {
        if (el.id === edge.id) {
          return { ...el, data: { ...el.data, relationship_type: newRelation } };
        }
        return el;
      });
      elements.value = updatedElements;

      // Save to backend
      try {
        const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/edge/${edge.data.edge_id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            relationship_type: newRelation
          })
        });

        if (response.ok) {
          emit('tree-updated');
          showSuccessMessage('Relationship updated successfully!');
        } else {
          throw new Error('Failed to update edge');
        }
      } catch (error) {
        showErrorMessage('Edge updated locally but failed to save. Please try saving the tree manually.');
      }

      emit('tree-updated');
    }
  } catch (error) {
    showErrorMessage('Failed to update relationship. Please try again.');
  }
};

const deleteEdge = async (edge: TreeEdge): Promise<void> => {
  try {
    if (confirm('Are you sure you want to delete this connection?')) {
      // Remove from local elements
      elements.value = elements.value.filter(el => el.id !== edge.id);

      // Delete from backend
      if (edge.data.edge_id) {
        try {
          const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/edge/${edge.data.edge_id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
          });

          if (response.ok) {
            emit('tree-updated');
            showSuccessMessage('Connection deleted successfully!');
          } else {
            throw new Error('Failed to delete edge');
          }
        } catch (error) {
          showErrorMessage('Edge removed locally but failed to delete from server. Please try saving the tree manually.');
        }
      }

      emit('tree-updated');
    }
  } catch (error) {
    showErrorMessage('Failed to delete connection. Please try again.');
  }
};

const handleEdgeContextMenu = (event: any, edge: TreeEdge): void => {
  event.preventDefault();
  
  // Create context menu
  const menu = document.createElement('div');
  menu.className = 'fixed bg-white border border-gray-300 rounded-md shadow-lg z-50 p-2';
  menu.style.left = event.clientX + 'px';
  menu.style.top = event.clientY + 'px';
  
  const editOption = document.createElement('div');
  editOption.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
  editOption.textContent = 'Edit Relationship';
  editOption.onclick = () => {
    handleEdgeDoubleClick(event, edge);
    document.body.removeChild(menu);
  };
  
  const deleteOption = document.createElement('div');
  deleteOption.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm text-red-600';
  deleteOption.textContent = 'Delete Connection';
  deleteOption.onclick = () => {
    deleteEdge(edge);
    document.body.removeChild(menu);
  };
  
  menu.appendChild(editOption);
  menu.appendChild(deleteOption);
  
  // Remove menu when clicking elsewhere
  const removeMenu = () => {
    if (document.body.contains(menu)) {
      document.body.removeChild(menu);
    }
    document.removeEventListener('click', removeMenu);
  };
  
  setTimeout(() => {
    document.addEventListener('click', removeMenu);
  }, 100);
  
  document.body.appendChild(menu);
};

const handlePaneClick = (): void => {
  // Clear selection on background click
  selectedElements.value = [];
  showSearchResults.value = false;
};

// Layout generation based on math.html example
const generateLayout = (): void => {
  isGeneratingLayout.value = true;
  
  setTimeout(() => {
    try {
      const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
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
        emit('tree-updated');
        showSuccessMessage('Layout generated successfully!');
      }
    } catch (error) {
      showErrorMessage('Failed to generate layout. Please try again.');
    } finally {
      isGeneratingLayout.value = false;
    }
  }, 500);
};

// Save tree functionality with layout saving
const saveTree = async (): Promise<void> => {
  isSaving.value = true;
  
  try {
    const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
    const edges = elements.value.filter(el => el.type === 'default') as TreeEdge[];
    
    // Prepare tree data for saving
    const treeData = {
      nodes: nodes.map(node => ({
        id: node.id,
        profile_id: node.data.id,
        relation: node.data.relation,
        x_position: Math.round(node.position.x),
        y_position: Math.round(node.position.y)
      })),
      edges: edges.map(edge => ({
        id: edge.id,
        from_node_id: parseInt(edge.source),
        to_node_id: parseInt(edge.target),
        relationship_type: edge.data.relationship_type || 'family',
        edge_type: 'bezier'
      }))
    };
    
    // Save tree state
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/save`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(treeData)
    });
    
    if (response.ok) {
      const result = await response.json();
      
      // Save current layout as custom layout
      await saveCustomLayout();
      
      // Show success message with details
      const successMessage = `Family tree saved successfully!\nNodes: ${result.nodes_count}\nEdges: ${result.edges_count}`;
      showSuccessMessage(successMessage);
    } else {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Failed to save tree');
    }
  } catch (error) {
    const errorMessage = error instanceof Error ? error.message : 'Failed to save family tree. Please try again.';
    showErrorMessage(errorMessage);
  } finally {
    isSaving.value = false;
  }
};

// Save custom layout
const saveCustomLayout = async (): Promise<void> => {
  try {
    const nodes = elements.value.filter(el => el.type === 'familyNode') as TreeNode[];
    
    const layoutData = {
      nodes: nodes.map(node => ({
        id: node.id,
        x: node.position.x,
        y: node.position.y
      })),
      timestamp: new Date().toISOString()
    };
    
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/layout`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        name: 'Custom Layout',
        type: 'custom',
        layout_data: layoutData
      })
    });
    
    if (!response.ok) {
      console.warn('Failed to save custom layout');
    }
  } catch (error) {
    console.warn('Error saving custom layout:', error);
  }
};

// Load custom layout
const loadCustomLayout = async (): Promise<void> => {
  try {
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/layout/custom`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (response.ok) {
      const layout = await response.json();
      if (layout.layout_data && layout.layout_data.nodes) {
        applyLayoutToNodes(layout.layout_data.nodes);
      }
    }
  } catch (error) {
    console.warn('Error loading custom layout:', error);
  }
};

// Apply layout to nodes
const applyLayoutToNodes = (layoutNodes: Array<{id: string, x: number, y: number}>): void => {
  elements.value = elements.value.map(el => {
    if (el.type === 'familyNode') {
      const layoutNode = layoutNodes.find(n => n.id === el.id);
      if (layoutNode) {
        return {
          ...el,
          position: { x: layoutNode.x, y: layoutNode.y }
        };
      }
    }
    return el;
  });
};

// Success message handler
const showSuccessMessage = (message: string): void => {
  successMessage.value = message;
  showSuccessPopup.value = true;
};

// Error message handler
const showErrorMessage = (message: string): void => {
  errorMessage.value = message;
  showErrorPopup.value = true;
};

// Keyboard shortcuts
const handleKeyDown = (event: KeyboardEvent): void => {
  try {
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
  } catch (error) {
    showErrorMessage('Failed to handle keyboard shortcut. Please try again.');
  }
};

onMounted(async (): Promise<void> => {
  // Set initial builder mode
  isBuilderMode.value = props.isEditable;
  
  // Create center node with improved positioning
  if (props.profileUserData && props.profileUserData.id) {
    // Calculate center position based on container size
    const containerWidth = 800;
    const containerHeight = 600;
    const centerX = containerWidth / 2;
    const centerY = containerHeight / 2;
    
    const centerNode: TreeNode = {
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
        date_of_birth: props.profileUserData.date_of_birth,
        location: props.profileUserData.location,
        bio: props.profileUserData.bio,
        profession: props.profileUserData.profession,
        passion: props.profileUserData.passion,
        mission: props.profileUserData.mission,
        calling: props.profileUserData.calling
      }
    };
    
    elements.value = [centerNode];
  }
  
  // Load existing tree data
  await loadTreeData();
  
  // Try to load custom layout if available
  await loadCustomLayout();
  
  // Add keyboard event listener
  document.addEventListener('keydown', handleKeyDown);
});

// Cleanup
onUnmounted((): void => {
  document.removeEventListener('keydown', handleKeyDown);
});

// Watch elements for changes
watch(elements, (): void => {
  // Auto-save functionality could be added here
}, { deep: true });

// Watch search query for debouncing
let searchTimeout: NodeJS.Timeout;
watch(searchQuery, (newQuery: string): void => {
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
