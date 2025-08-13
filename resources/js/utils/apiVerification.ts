/**
 * API Endpoint Verification Utility
 * This utility checks if the required family tree API endpoints exist and are accessible
 */

interface ApiEndpoint {
  path: string;
  method: string;
  description: string;
  required: boolean;
}

interface ApiVerificationResult {
  endpoint: string;
  exists: boolean;
  accessible: boolean;
  error?: string;
}

export const FAMILY_TREE_ENDPOINTS: ApiEndpoint[] = [
  {
    path: '/api/profiles/{id}/familytree',
    method: 'GET',
    description: 'Load family tree data',
    required: true
  },
  {
    path: '/api/profiles/{id}/familytree/search',
    method: 'GET',
    description: 'Search profiles for family tree',
    required: true
  },
  {
    path: '/api/profiles/{id}/familytree/save',
    method: 'POST',
    description: 'Save family tree data',
    required: true
  },
  {
    path: '/api/profiles/{id}/familytree/create-profile',
    method: 'POST',
    description: 'Create new profile and add to tree',
    required: true
  }
];

/**
 * Verify if an API endpoint exists and is accessible
 */
export async function verifyEndpoint(
  baseUrl: string,
  endpoint: string,
  method: string = 'GET'
): Promise<ApiVerificationResult> {
  const url = `${baseUrl}${endpoint}`;
  
  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    return {
      endpoint,
      exists: true,
      accessible: response.status !== 404 && response.status !== 500,
      error: response.status >= 400 ? `HTTP ${response.status}` : undefined
    };
  } catch (error) {
    return {
      endpoint,
      exists: false,
      accessible: false,
      error: error instanceof Error ? error.message : 'Unknown error'
    };
  }
}

/**
 * Verify all required family tree API endpoints
 */
export async function verifyFamilyTreeEndpoints(
  profileId: number,
  baseUrl: string = window.location.origin
): Promise<ApiVerificationResult[]> {
  const results: ApiVerificationResult[] = [];
  
  for (const endpoint of FAMILY_TREE_ENDPOINTS) {
    const path = endpoint.path.replace('{id}', profileId.toString());
    const result = await verifyEndpoint(baseUrl, path, endpoint.method);
    results.push(result);
  }
  
  return results;
}

/**
 * Check if all required endpoints are available
 */
export function checkEndpointsAvailability(results: ApiVerificationResult[]): {
  allAvailable: boolean;
  missingEndpoints: string[];
  errors: string[];
} {
  const missingEndpoints: string[] = [];
  const errors: string[] = [];
  
  for (const result of results) {
    if (!result.exists || !result.accessible) {
      missingEndpoints.push(result.endpoint);
      if (result.error) {
        errors.push(`${result.endpoint}: ${result.error}`);
      }
    }
  }
  
  return {
    allAvailable: missingEndpoints.length === 0,
    missingEndpoints,
    errors
  };
}

/**
 * Display endpoint verification results in console
 */
export function logEndpointVerification(results: ApiVerificationResult[]): void {
  console.group('🔍 Family Tree API Endpoint Verification');
  
  const availability = checkEndpointsAvailability(results);
  
  if (availability.allAvailable) {
    console.log('✅ All required endpoints are available');
  } else {
    console.warn('⚠️ Some endpoints are missing or inaccessible:');
    availability.missingEndpoints.forEach(endpoint => {
      console.warn(`   - ${endpoint}`);
    });
  }
  
  if (availability.errors.length > 0) {
    console.error('❌ Errors encountered:');
    availability.errors.forEach(error => {
      console.error(`   - ${error}`);
    });
  }
  
  results.forEach(result => {
    const status = result.exists && result.accessible ? '✅' : '❌';
    console.log(`${status} ${result.method} ${result.endpoint}`);
  });
  
  console.groupEnd();
}

/**
 * Initialize endpoint verification on component mount
 */
export async function initializeApiVerification(profileId: number): Promise<void> {
  try {
    const results = await verifyFamilyTreeEndpoints(profileId);
    logEndpointVerification(results);
    
    const availability = checkEndpointsAvailability(results);
    if (!availability.allAvailable) {
      console.warn('Family tree functionality may not work properly due to missing API endpoints.');
      console.warn('Please ensure the backend routes are properly configured.');
    }
  } catch (error) {
    console.error('Failed to verify API endpoints:', error);
  }
}