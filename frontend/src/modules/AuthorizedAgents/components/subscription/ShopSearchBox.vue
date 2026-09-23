<template>
  <div class="search-box-card">
    <div class="card-label mb-3">
      <i class="bi bi-search me-2 text-primary"></i>
      <span>Search Shop</span>
    </div>

    <div class="search-input-wrapper" ref="wrapperRef">
      <div class="input-group" :class="{ focused: isFocused }">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-building text-muted"></i>
        </span>
        <input
          type="text"
          class="form-control border-start-0 border-end-0 ps-0"
          placeholder="Enter Shop Name or Entity ID..."
          v-model="searchQuery"
          @input="onInput"
          @focus="
            isFocused = true;
            showDropdown = store.searchResults.length > 0;
          "
          @blur="isFocused = false"
          autocomplete="off"
        />
        <!-- Loading spinner inside input -->
        <span v-if="store.isSearching" class="input-group-text bg-white border-start-0">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </span>
        <!-- Clear button -->
        <button
          v-else-if="searchQuery"
          class="btn btn-outline-secondary border-start-0"
          type="button"
          @click="clearSearch"
        >
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- Dropdown -->
      <transition name="dropdown-fade">
        <!-- Results -->
        <div
          v-if="showDropdown && store.searchResults.length > 0"
          class="search-dropdown"
        >
          <div class="dropdown-header">
            {{ store.searchResults.length }} shop{{
              store.searchResults.length > 1 ? "s" : ""
            }}
            found
          </div>
          <div
            v-for="shop in store.searchResults"
            :key="shop.entityId"
            class="dropdown-item-custom"
            @mousedown.prevent="selectShop(shop)"
          >
            <div class="d-flex align-items-center gap-3">
              <div class="shop-avatar">
                {{ getInitial(shop) }}
              </div>
              <div class="flex-grow-1 overflow-hidden">
                <div class="shop-name text-truncate">{{ shop.shopName }}</div>
                <div class="shop-meta">
                  <span class="entity-id">{{ shop.entityId }}</span>
                  <span class="separator">•</span>
                  <span class="text-truncate">{{ shop.businessName }}</span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <SubscriptionBadge :status="shop.subscriptionStatus" size="sm" />
              </div>
            </div>
          </div>
        </div>

        <!-- No results -->
        <div
          v-else-if="showDropdown && searchQuery.length >= 2 && !store.isSearching"
          class="search-dropdown"
        >
          <div class="no-results">
            <template v-if="store.searchError">
              <i class="bi bi-exclamation-circle me-2 text-danger"></i>
              <span class="text-danger">{{ store.searchError }}</span>
            </template>
            <template v-else>
              <i class="bi bi-search me-2"></i>
              No shops found for "<strong>{{ searchQuery }}</strong
              >"
            </template>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import SubscriptionBadge from "@/modules/AuthorizedAgents/components/subscription/SubscriptionBadge.vue";
import { useAssignSubscriptionStore } from "@/modules/AuthorizedAgents/stores/useAssignSubscriptionStore";

const emit = defineEmits(["shopSelected", "cleared"]);

const store = useAssignSubscriptionStore();
const searchQuery = ref("");
const showDropdown = ref(false);
const isFocused = ref(false);
const wrapperRef = ref(null);

const getInitial = (shop) => {
  const name = shop?.shopName || shop?.businessName || shop?.name || "?";
  return String(name).charAt(0).toUpperCase();
};

let debounceTimer = null;
const onInput = () => {
  showDropdown.value = false;
  clearTimeout(debounceTimer);

  if (searchQuery.value.length < 2) {
    store.searchResults = [];
    return;
  }

  debounceTimer = setTimeout(async () => {
    await store.searchShop(searchQuery.value);
    showDropdown.value = true;
  }, 400);
};

const selectShop = (shop) => {
  const label = shop?.shopName || shop?.businessName || "—";
  searchQuery.value = `${shop?.entityId ?? ""} — ${label}`;
  showDropdown.value = false;
  emit("shopSelected", shop);
};

const clearSearch = () => {
  searchQuery.value = "";
  showDropdown.value = false;
  store.searchResults = [];
  emit("cleared");
};

const handleClickOutside = (e) => {
  if (wrapperRef.value && !wrapperRef.value.contains(e.target)) {
    showDropdown.value = false;
  }
};

onMounted(() => document.addEventListener("mousedown", handleClickOutside));
onUnmounted(() => document.removeEventListener("mousedown", handleClickOutside));
</script>

<style scoped>
.search-box-card {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
  border: 1px solid #f0f0f0;
}
.card-label {
  font-weight: 600;
  font-size: 0.95rem;
  color: #495057;
}
.search-input-wrapper {
  position: relative;
}
.input-group {
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input-group.focused {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.input-group .form-control,
.input-group .input-group-text,
.input-group .btn {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}
.form-control:focus {
  box-shadow: none;
}

/* Dropdown */
.search-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  z-index: 1050;
  max-height: 320px;
  overflow-y: auto;
  padding: 6px 0;
}
.dropdown-header {
  padding: 8px 16px 4px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #adb5bd;
}
.dropdown-item-custom {
  padding: 10px 16px;
  cursor: pointer;
  transition: background 0.15s;
}
.dropdown-item-custom:hover {
  background: #f8f9ff;
}
.shop-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: #fff;
  font-weight: 700;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.shop-name {
  font-weight: 600;
  font-size: 0.88rem;
  color: #212529;
}
.shop-meta {
  font-size: 0.75rem;
  color: #868e96;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 1px;
}
.entity-id {
  font-family: monospace;
  color: #0d6efd;
  flex-shrink: 0;
}
.separator {
  color: #ced4da;
}
.no-results {
  padding: 20px 16px;
  text-align: center;
  color: #adb5bd;
  font-size: 0.88rem;
}

/* Dropdown animation */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: all 0.2s ease;
}
.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
