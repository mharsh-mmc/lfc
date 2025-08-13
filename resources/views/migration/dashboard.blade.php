@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">🌳 Family Tree Migration Dashboard</h1>
            <p class="text-lg text-gray-600">Transform your old family tree data to the new VueFlow system</p>
            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                <p class="text-green-800 font-medium">🚀 Complete Database Migration: ALL Tables Included</p>
                <p class="text-green-600 text-sm">Every piece of data from your old system will be migrated</p>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Old Database Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📥 Old Database Status</h2>
                <div id="old-database-status">
                    @if($oldDatabaseStats)
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="text-green-600 font-semibold">✅ Imported</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total People:</span>
                                <span class="font-semibold">{{ $oldDatabaseStats['total_people'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Trees:</span>
                                <span class="font-semibold">{{ $oldDatabaseStats['total_trees'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tree People:</span>
                                <span class="font-semibold">{{ $oldDatabaseStats['total_tree_people'] }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 mb-4">Old database not imported yet</p>
                            <button id="import-old-database" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Import Old Database
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- New Database Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">🆕 New Database Status</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Users:</span>
                        <span class="font-semibold">{{ $newDatabaseStats['total_users'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Family Trees:</span>
                        <span class="font-semibold">{{ $newDatabaseStats['total_family_trees'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tree Nodes:</span>
                        <span class="font-semibold">{{ $newDatabaseStats['total_tree_nodes'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tree Edges:</span>
                        <span class="font-semibold">{{ $newDatabaseStats['total_tree_edges'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migration Actions -->
        @if($oldDatabaseStats)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🔄 Migration Actions</h2>
            
            <!-- What Gets Migrated -->
            <div class="mb-6 p-4 bg-green-50 rounded-lg">
                <h3 class="text-lg font-medium text-green-800 mb-2">✅ What Will Be Migrated (ALL Tables):</h3>
                <ul class="text-green-700 space-y-1">
                    <li>• <strong>Users:</strong> All people from anagrafica table → users table</li>
                    <li>• <strong>Family Trees:</strong> All active trees with nodes and relationships</li>
                    <li>• <strong>Education Records:</strong> All titolo di studio → education table</li>
                    <li>• <strong>Deceased Profiles:</strong> All death records → deceased_profiles table</li>
                    <li>• <strong>Media Files:</strong> All images, photos, curriculum → media table</li>
                    <li>• <strong>Cities Data:</strong> All city information preserved</li>
                    <li>• <strong>Extra Metadata:</strong> All additional tables and configurations</li>
                    <li>• <strong>VueFlow Data:</strong> Position-based → coordinate-based layouts</li>
                </ul>
            </div>
            
            <!-- Migration Benefits -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="text-lg font-medium text-blue-800 mb-2">🎯 Migration Benefits:</h3>
                <ul class="text-blue-700 space-y-1">
                    <li>• <strong>100% Data Preservation:</strong> No information is lost</li>
                    <li>• <strong>Complete System:</strong> All features from old system available</li>
                    <li>• <strong>Modern Interface:</strong> VueFlow-powered family trees</li>
                    <li>• <strong>Enhanced Features:</strong> Better user experience with all data</li>
                </ul>
            </div>

            <!-- Available Trees -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-3">Available Trees for Migration</h3>
                <div id="available-trees" class="space-y-2">
                    <!-- Trees will be loaded here -->
                </div>
            </div>

            <!-- Migration Buttons -->
            <div class="flex flex-wrap gap-4">
                <button id="migrate-complete-database" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-colors font-medium">
                    🚀 Migrate Complete Database (ALL Tables)
                </button>
                <button id="refresh-status" class="bg-gray-600 text-white px-6 py-3 rounded-md hover:bg-gray-700 transition-colors font-medium">
                    🔄 Refresh Status
                </button>
            </div>
        </div>
        @endif

        <!-- Migration Progress -->
        <div id="migration-progress" class="hidden bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📊 Migration Progress</h2>
            <div id="progress-content">
                <!-- Progress will be shown here -->
            </div>
        </div>

        <!-- Migration Results -->
        <div id="migration-results" class="hidden bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">✅ Migration Results</h2>
            <div id="results-content">
                <!-- Results will be shown here -->
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-lg font-medium text-gray-800" id="loading-message">Processing...</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Import old database
    document.getElementById('import-old-database')?.addEventListener('click', function() {
        importOldDatabase();
    });

    // Migrate complete database
    document.getElementById('migrate-complete-database')?.addEventListener('click', function() {
        migrateCompleteDatabase();
    });

    // Refresh status
    document.getElementById('refresh-status')?.addEventListener('click', function() {
        refreshStatus();
    });

    // Load available trees if old database is imported
    if (document.getElementById('available-trees')) {
        loadAvailableTrees();
    }
});

function showLoading(message = 'Processing...') {
    document.getElementById('loading-message').textContent = message;
    document.getElementById('loading-modal').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loading-modal').classList.add('hidden');
}

function showNotification(message, type = 'success') {
    // You can implement a notification system here
    alert(message);
}

async function importOldDatabase() {
    try {
        showLoading('Importing old database structure...');
        
        const response = await fetch('{{ route("migration.import-old") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            location.reload(); // Refresh page to show new status
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Failed to import old database: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

async function loadAvailableTrees() {
    try {
        const response = await fetch('{{ route("migration.available-trees") }}');
        const result = await response.json();
        
        if (result.success) {
            const treesContainer = document.getElementById('available-trees');
            treesContainer.innerHTML = '';
            
            result.data.forEach(tree => {
                const treeElement = document.createElement('div');
                treeElement.className = 'flex justify-between items-center p-3 bg-gray-50 rounded-md';
                treeElement.innerHTML = `
                    <div>
                        <span class="font-medium">${tree.title}</span>
                        <span class="text-sm text-gray-500 ml-2">(ID: ${tree.id})</span>
                    </div>
                    <span class="text-sm text-green-600">Will be migrated with complete database</span>
                `;
                treesContainer.appendChild(treeElement);
            });
        }
    } catch (error) {
        console.error('Failed to load available trees:', error);
    }
}

async function migrateCompleteDatabase() {
    if (!confirm('Are you sure you want to migrate the COMPLETE database? This will migrate ALL tables including:\n\n• Users and Family Trees\n• Education Records\n• Deceased Profiles\n• Media Files\n• Cities Data\n• Extra Metadata Tables\n\nThis process may take some time.')) {
        return;
    }

    try {
        showLoading('Migrating complete database (ALL tables)...');
        
        const response = await fetch('{{ route("migration.migrate-complete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            showMigrationResults([{
                status: 'success',
                message: result.message,
                data: result.data
            }]);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Failed to migrate complete database: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

function showMigrationResults(results) {
    const resultsContainer = document.getElementById('migration-results');
    const contentContainer = document.getElementById('results-content');
    
    contentContainer.innerHTML = '';
    
    results.forEach(result => {
        const resultElement = document.createElement('div');
        resultElement.className = 'p-4 border rounded-md mb-3 border-green-200 bg-green-50';
        
        if (result.status === 'success') {
            resultElement.innerHTML = `
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="text-green-600 text-2xl mr-2">✅</span>
                        <span class="font-medium text-green-800">Complete Database Migration Successful!</span>
                    </div>
                    <p class="text-green-700">${result.message}</p>
                    <div class="text-sm text-green-600">
                        <p>• Users migrated: ${result.data.users_migrated}</p>
                        <p>• Family trees migrated: ${result.data.family_trees_migrated}</p>
                        <p>• Education records: ${result.data.education_records}</p>
                        <p>• Deceased profiles: ${result.data.deceased_profiles}</p>
                        <p>• Media files: ${result.data.media_files}</p>
                        <p>• Cities data: ${result.data.cities}</p>
                        <p>• Additional tables: ${result.data.additional_tables}</p>
                        <p>• All additional tables and metadata included</p>
                    </div>
                </div>
            `;
        } else {
            resultElement.className = 'p-4 border rounded-md mb-3 border-red-200 bg-red-50';
            resultElement.innerHTML = `
                <div class="flex items-center">
                    <span class="text-red-600 text-2xl mr-2">❌</span>
                    <span class="font-medium text-red-800">Migration Failed</span>
                </div>
                <p class="text-red-700 mt-2">${result.message}</p>
            `;
        }
        
        contentContainer.appendChild(resultElement);
    });
    
    resultsContainer.classList.remove('hidden');
}

async function refreshStatus() {
    try {
        const response = await fetch('{{ route("migration.status") }}');
        const result = await response.json();
        
        if (result.migration_ready) {
            location.reload();
        } else {
            showNotification('Migration not ready yet. Please import old database first.', 'info');
        }
    } catch (error) {
        showNotification('Failed to refresh status: ' + error.message, 'error');
    }
}
</script>
@endpush