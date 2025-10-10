import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export function usePageTracking() {
  const router = useRouter();
  const startTime = ref(null);
  const currentPageUrl = ref('');
  const currentPageTitle = ref('');

  const sendPageVisit = async (pageUrl, pageTitle, timeSpent) => {
    try {
      const response = await axios.post('/api/page-visits', {
        pageUrl,
        pageTitle,
        timeSpent: Math.round(timeSpent / 1000) // Convert to seconds
      });
      console.log('✅ Page visit tracked:', pageUrl, `${Math.round(timeSpent / 1000)}s`);
      return response;
    } catch (error) {
      // Silently fail - don't disrupt user experience
      if (error.response?.status !== 401) {
        console.warn('❌ Failed to track page visit:', error.response?.data || error.message);
      } else {
        console.log('ℹ️ User not authenticated, skipping page tracking');
      }
    }
  };

  const trackPageVisit = () => {
    // Send previous page data if exists
    if (startTime.value && currentPageUrl.value) {
      const timeSpent = Date.now() - startTime.value;
      // Only track if user spent more than 1 second on the page
      if (timeSpent > 1000) {
        sendPageVisit(currentPageUrl.value, currentPageTitle.value, timeSpent);
      }
    }

    // Start tracking new page
    startTime.value = Date.now();
    currentPageUrl.value = window.location.pathname;
    currentPageTitle.value = document.title;
  };

  const handleBeforeUnload = () => {
    if (startTime.value && currentPageUrl.value) {
      const timeSpent = Date.now() - startTime.value;
      if (timeSpent > 1000) {
        // Use sendBeacon for reliable tracking on page unload
        const blob = new Blob([JSON.stringify({
          pageUrl: currentPageUrl.value,
          pageTitle: currentPageTitle.value,
          timeSpent: Math.round(timeSpent / 1000)
        })], { type: 'application/json' });

        navigator.sendBeacon('/api/page-visits', blob);
        console.log('📤 Final page visit sent via beacon');
      }
    }
  };

  const startTracking = () => {
    // Track initial page
    trackPageVisit();

    // Track on route change
    router.afterEach(() => {
      trackPageVisit();
    });

    // Track before leaving the site
    window.addEventListener('beforeunload', handleBeforeUnload);
  };

  const stopTracking = () => {
    window.removeEventListener('beforeunload', handleBeforeUnload);

    // Send final page visit data
    if (startTime.value && currentPageUrl.value) {
      const timeSpent = Date.now() - startTime.value;
      if (timeSpent > 1000) {
        sendPageVisit(currentPageUrl.value, currentPageTitle.value, timeSpent);
      }
    }
  };

  onMounted(() => {
    startTracking();
  });

  onBeforeUnmount(() => {
    stopTracking();
  });

  return {
    startTracking,
    stopTracking
  };
}
