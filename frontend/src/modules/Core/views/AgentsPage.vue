<template>
  <div class="rb-home-page">
    <Breadcrumb
      :title="$t('common.Authorized Agents')"
      :subtitle="$t('common.authorized_agent_page_subtitle')"
    />

    <section>
      <div class="container">
        <!-- Search Bar -->
        <div class="row mb-4 align-items-end g-2">
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">{{ $t("common.filter_by") }}</label>
            <select
              v-model="agentStore.filter_option"
              class="form-select"
              @change="onFilterChange"
            >
              <option value="name">{{ $t("common.name") }}</option>
              <option value="email">{{ $t("common.email") }}</option>
              <option value="mobile">{{ $t("common.mobile") }}</option>
              <option value="address">{{ $t("common.address") }}</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">{{ $t("common.search") }}</label>
            <input
              v-model="agentStore.searchValue"
              type="text"
              class="form-control"
              :placeholder="`${$t('common.search_by')} ${agentStore.filter_option}...`"
              @input="onSearch"
            />
          </div>

          <div class="col-12 col-md-2">
            <button
              class="btn btn-outline-secondary w-100"
              @click="agentStore.resetFilters"
            >
              {{ $t("common.reset") }}
            </button>
          </div>
        </div>

        <!-- Loading Skeleton -->
        <template v-if="agentStore.loading">
          <div class="row mb-4" v-for="n in 3" :key="n">
            <div class="col-12">
              <div class="card agent-skeleton p-3">
                <div class="row g-0">
                  <div class="col-md-4">
                    <div class="skeleton skeleton-image"></div>
                  </div>
                  <div class="col-md-8 ps-3">
                    <div class="skeleton skeleton-text w-50 mb-3"></div>
                    <div class="skeleton skeleton-text w-75 mb-3"></div>
                    <div class="skeleton skeleton-text w-100 mb-3"></div>
                    <div class="skeleton skeleton-text w-40 mb-3"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Error State -->
        <div v-else-if="agentStore.error" class="alert alert-danger text-center">
          {{ agentStore.error }}
          <button
            class="btn btn-sm btn-outline-danger ms-3"
            @click="agentStore.fetchAgents()"
          >
            {{ $t("common.retry") }}
          </button>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!agentStore.loading && agentStore.agents.length === 0"
          class="text-center py-5"
        >
          <i class="bi bi-person-x fs-1 text-muted"></i>
          <p class="text-muted mt-3">{{ $t("common.no_agents_found") }}</p>
          <button class="btn btn-outline-primary mt-2" @click="agentStore.resetFilters">
            {{ $t("common.reset") }}
          </button>
        </div>

        <!-- Agent Cards -->
        <template v-else>
          <div class="row mb-4" v-for="agent in agentStore.agents" :key="agent.user_id">
            <div class="col-12">
              <AgentCard :agent="agent" />
            </div>
          </div>
        </template>

        <!-- Pagination -->
        <div v-if="!agentStore.loading && agentStore.agents.length > 0" class="row mt-3">
          <div
            class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2"
          >
            <span class="text-muted small">
              {{ $t("common.showing") }}
              {{ agentStore.start + 1 }}–{{
                Math.min(agentStore.start + agentStore.length, agentStore.totalRecords)
              }}
              {{ $t("common.of") }} {{ agentStore.totalRecords }}
            </span>
            <div class="d-flex gap-2">
              <button
                class="btn btn-outline-primary btn-sm"
                :disabled="agentStore.start === 0"
                @click="prevPage"
              >
                &laquo; {{ $t("common.prev") }}
              </button>
              <button
                class="btn btn-outline-primary btn-sm"
                :disabled="
                  agentStore.start + agentStore.length >= agentStore.totalRecords
                "
                @click="nextPage"
              >
                {{ $t("common.next") }} &raquo;
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <MobileAppSection />
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useAgentStore } from "@/modules/AuthorizedAgents/stores/agentStore";

import Breadcrumb from "../components/Breadcrumb.vue";
import AgentCard from "../components/agents/AgentCard.vue";
import MobileAppSection from "../components/home/MobileAppSection.vue";

import "../assets/css/home/hero.css";
import "../assets/css/home/feature-card.css";

const agentStore = useAgentStore();

let searchDebounce = null;

onMounted(() => {
  agentStore.fetchAgents();
});

function onSearch() {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    agentStore.start = 0;
    agentStore.fetchAgents();
  }, 400);
}

function onFilterChange() {
  agentStore.searchValue = "";
  agentStore.start = 0;
  agentStore.fetchAgents();
}

function prevPage() {
  agentStore.start = Math.max(0, agentStore.start - agentStore.length);
  agentStore.fetchAgents({ start: agentStore.start });
}

function nextPage() {
  agentStore.start += agentStore.length;
  agentStore.fetchAgents({ start: agentStore.start });
}
</script>

<style scoped>
.rb-home-page {
  overflow-x: hidden;
}

/* Skeleton */
@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  border-radius: 6px;
}
.skeleton-image {
  height: 180px;
  width: 100%;
  border-radius: 8px;
}
.skeleton-text {
  height: 16px;
  margin-bottom: 12px;
}
.w-40 {
  width: 40%;
}
.agent-skeleton {
  border-radius: 8px;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
