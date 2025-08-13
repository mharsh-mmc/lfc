import { ref, computed, readonly } from 'vue'
import { router } from '@inertiajs/vue3'

interface Profile {
  id: number
  name: string
  years_lived: string
  age_at_death: number
  memorial_message: string
  profile_photo_url: string
  profile_photo_thumbnail_url: string
  created_at: string
  created_by: number
  creator?: {
    name: string
  }
}

interface ProfileStats {
  total: number
  public: number
  private: number
  recent: number
}

export function useProfiles() {
  const profiles = ref<Profile[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const stats = ref<ProfileStats | null>(null)
  const searchQuery = ref('')
  const currentPage = ref(1)
  const hasMore = ref(true)

  // Cache for API responses
  const cache = new Map<string, any>()

  const filteredProfiles = computed(() => {
    if (!searchQuery.value) {
      return profiles.value
    }
    
    const query = searchQuery.value.toLowerCase()
    return profiles.value.filter(profile => 
      profile.name.toLowerCase().includes(query) ||
      (profile.memorial_message && profile.memorial_message.toLowerCase().includes(query))
    )
  })

  const fetchProfiles = async (page = 1, perPage = 12) => {
    const cacheKey = `profiles_${page}_${perPage}`
    
    if (cache.has(cacheKey)) {
      const cached = cache.get(cacheKey)
      profiles.value = cached.data
      hasMore.value = cached.meta.has_more
      return
    }

    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/deceased-profiles?page=${page}&per_page=${perPage}`)
      const data = await response.json()

      if (response.ok) {
        if (page === 1) {
          profiles.value = data.data
        } else {
          profiles.value = [...profiles.value, ...data.data]
        }
        
        hasMore.value = data.meta.has_more
        currentPage.value = page

        // Cache the response
        cache.set(cacheKey, data)
      } else {
        error.value = data.error || 'Failed to fetch profiles'
      }
    } catch (err) {
      error.value = 'Network error occurred'
    } finally {
      loading.value = false
    }
  }

  const searchProfiles = async (query: string) => {
    searchQuery.value = query
    
    if (!query.trim()) {
      await fetchProfiles(1)
      return
    }

    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/search/profiles?q=${encodeURIComponent(query)}`)
      const data = await response.json()

      if (response.ok) {
        profiles.value = data.data
        hasMore.value = false // Search results don't support pagination
      } else {
        error.value = data.error || 'Search failed'
      }
    } catch (err) {
      error.value = 'Search error occurred'
    } finally {
      loading.value = false
    }
  }

  const fetchUserProfiles = async (page = 1, perPage = 12) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/user/profiles?page=${page}&per_page=${perPage}`)
      const data = await response.json()

      if (response.ok) {
        if (page === 1) {
          profiles.value = data.data
        } else {
          profiles.value = [...profiles.value, ...data.data]
        }
        
        hasMore.value = data.meta.current_page < data.meta.last_page
        currentPage.value = page
      } else {
        error.value = data.error || 'Failed to fetch user profiles'
      }
    } catch (err) {
      error.value = 'Network error occurred'
    } finally {
      loading.value = false
    }
  }

  const fetchStats = async () => {
    try {
      const response = await fetch('/api/user/stats')
      const data = await response.json()

      if (response.ok) {
        stats.value = data.data
      }
    } catch (err) {
      console.error('Failed to fetch stats:', err)
    }
  }

  const loadMore = async () => {
    if (loading.value || !hasMore.value) return
    
    const nextPage = currentPage.value + 1
    await fetchProfiles(nextPage)
  }

  const viewProfile = (profile: Profile) => {
    router.visit(`/deceased-profiles/${profile.id}`)
  }

  const editProfile = (profile: Profile) => {
    router.visit(`/deceased-profiles/${profile.id}/edit`)
  }

  const deleteProfile = async (profile: Profile) => {
    if (!confirm('Are you sure you want to delete this memorial profile? This action cannot be undone.')) {
      return
    }

    try {
      await router.delete(`/deceased-profiles/${profile.id}`)
      
      // Remove from local state
      profiles.value = profiles.value.filter(p => p.id !== profile.id)
      
      // Update stats
      await fetchStats()
    } catch (err) {
      alert('Failed to delete memorial profile. Please try again.')
    }
  }

  const clearCache = () => {
    cache.clear()
  }

  return {
    profiles: readonly(profiles),
    filteredProfiles,
    loading: readonly(loading),
    error: readonly(error),
    stats: readonly(stats),
    searchQuery: readonly(searchQuery),
    hasMore: readonly(hasMore),
    fetchProfiles,
    searchProfiles,
    fetchUserProfiles,
    fetchStats,
    loadMore,
    viewProfile,
    editProfile,
    deleteProfile,
    clearCache
  }
}
