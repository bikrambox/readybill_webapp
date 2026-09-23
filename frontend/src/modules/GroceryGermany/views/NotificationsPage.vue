<template>
  <div class="notifications-page">
    <PageHeader title="Notifications" :breadcrumbs="breadcrumbs" />

    <section class="section">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <!-- Header Actions -->
              <div
                class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3"
              >
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h5 class="mb-0">{{ $t('common.All Notifications') }}</h5>
                  <span
                    v-if="unreadCount > 0"
                    class="badge bg-primary rounded-pill"
                  >
                    {{ unreadCount }} New
                  </span>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button
                    v-if="unreadCount > 0"
                    class="btn btn-sm btn-outline-primary"
                    @click="markAllAsRead"
                  >
                    <i class="bi bi-check-all me-1"></i>
                    {{ $t('common.Mark all as read') }}
                  </button>

                  <div class="btn-group" role="group">
                    <button
                      type="button"
                      class="btn btn-sm"
                      :class="
                        filter === 'all'
                          ? 'btn-primary'
                          : 'btn-outline-secondary'
                      "
                      @click="filter = 'all'"
                    >
                      {{ $t('common.All') }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm"
                      :class="
                        filter === 'unread'
                          ? 'btn-primary'
                          : 'btn-outline-secondary'
                      "
                      @click="filter = 'unread'"
                    >
                      {{ $t('common.Unread') }}
                    </button>
                  </div>

                  <button
                    v-if="filteredNotifications.length > 0"
                    class="btn btn-sm btn-outline-danger"
                    @click="clearAll"
                  >
                    <i class="bi bi-trash me-1"></i>
                    {{ $t('common.Clear all') }}
                  </button>
                </div>
              </div>

              <!-- Notification List -->
              <div v-if="isLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
                </div>
              </div>

              <div
                v-else-if="filteredNotifications.length === 0"
                class="text-center py-5"
              >
                <i class="bi bi-bell-slash display-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ $t('common.No notifications') }}</h5>
                <p class="text-muted">
                  {{
                    filter === "unread"
                      ? "You have no unread notifications"
                      : "You have no notifications yet"
                  }}
                </p>
              </div>

              <div v-else class="notification-list-page">
                <NotificationCard
                  v-for="notification in paginatedNotifications"
                  :key="notification.id"
                  :notification="notification"
                  @click="handleNotificationClick(notification)"
                  @delete="handleDelete(notification.id)"
                />
              </div>

              <!-- Pagination -->
              <nav
                v-if="totalPages > 1"
                aria-label="Notifications pagination"
                class="mt-4"
              >
                <ul class="pagination justify-content-center mb-0">
                  <li
                    class="page-item"
                    :class="{ disabled: currentPage === 1 }"
                  >
                    <button
                      class="page-link"
                      @click="currentPage--"
                      :disabled="currentPage === 1"
                    >
                      {{ $t('common.Previous') }}
                    </button>
                  </li>

                  <li
                    v-for="page in visiblePages"
                    :key="page"
                    class="page-item"
                    :class="{ active: page === currentPage }"
                  >
                    <button class="page-link" @click="currentPage = page">
                      {{ page }}
                    </button>
                  </li>

                  <li
                    class="page-item"
                    :class="{ disabled: currentPage === totalPages }"
                  >
                    <button
                      class="page-link"
                      @click="currentPage++"
                      :disabled="currentPage === totalPages"
                    >
                      {{ $t('common.Next') }}
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useNotificationStore } from "@/modules/GroceryGermany/stores/notificationStore";
// import PageHeader from '../components/PageHeader.vue'
import NotificationCard from "@/modules/GroceryGermany/components/NotificationCard.vue";

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const router = useRouter();
const notificationStore = useNotificationStore();

// State
const filter = ref("all");
const currentPage = ref(1);
const pageSize = ref(10);
const isLoading = ref(false);

// Breadcrumbs
const breadcrumbs = [
  { label: "common.Home", to: "/home" },
  { label: "common.Notifications", active: true },
];

// Computed
const notifications = computed(() => notificationStore.notifications);
const unreadCount = computed(() => notificationStore.computedUnreadCount)

const filteredNotifications = computed(() => {
  if (filter.value === "unread") {
    return notifications.value.filter((n) => !n.is_read);
  }
  return notifications.value;
});

const paginatedNotifications = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredNotifications.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredNotifications.value.length / pageSize.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);

  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Methods
const loadNotifications = async () => {
  try {
    isLoading.value = true;
    await notificationStore.fetchNotifications();
  } catch (error) {
    console.error("Error loading notifications:", error);
  } finally {
    isLoading.value = false;
  }
};

const handleNotificationClick = (notification) => {
  notificationStore.markAsRead(notification.id)
  const route = notificationStore.routeMap[notification.type]
  if (route) {
    router.push(route)
  }
};



const markAllAsRead = async () => {
  try {
    await notificationStore.markAllAsRead();
  } catch (error) {
    console.error("Error marking all as read:", error);
  }
};

const handleDelete = async (id) => {
  if (!confirm(t('common.Are you sure you want to delete this notification')+"?")) {
    return;
  }

  try {
    await notificationStore.deleteNotification(id);
  } catch (error) {
    console.error("Error deleting notification:", error);
  }
};

const clearAll = async () => {
  if (
    !confirm(
      t('common.Are you sure you want to clear all notifications')+"?"+ t('common.This action cannot be undone') +"."
    )
  ) {
    return;
  }

  try {
    await notificationStore.clearAll();
  } catch (error) {
    console.error("Error clearing notifications:", error);
  }
};

// Lifecycle
onMounted(() => {
  loadNotifications();
});
</script>

<style scoped>
.notifications-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.notification-list-page {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .d-flex.justify-content-between {
    flex-direction: column;
    align-items: flex-start !important;
  }

  .btn-group {
    width: 100%;
  }

  .btn-group .btn {
    flex: 1;
  }
}
</style>