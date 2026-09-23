<template>
  <div ref="wrapperRef" class="tooltip-wrap" v-bind="triggerListeners">
    <slot />
  </div>

  <!-- Teleport to body — never clipped by any parent overflow -->
  <Teleport to="body">
    <Transition :name="`tt-${resolvedPlacement}`">
      <div
        v-if="visible"
        ref="tooltipRef"
        class="tt-box"
        :class="resolvedPlacement"
        role="tooltip"
        :style="tooltipStyle"
      >
        <div v-if="title" class="tt-title">{{ title }}</div>
        <p class="tt-msg">{{ message }}</p>
        <span class="tt-arrow" />
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from "vue";

const props = defineProps({
  title: { type: String, default: "" },
  message: { type: String, required: true },
  placement: { type: String, default: "top" }, // top | bottom | left | right
  trigger: { type: String, default: "hover" }, // hover | click | both
});

const visible = ref(false);
const wrapperRef = ref(null);
const tooltipRef = ref(null);
const tooltipStyle = ref({});
const resolvedPlacement = ref(props.placement); // may flip if near edge

const GAP = 9; // px between trigger and tooltip box

/* ── Position calculator ── */
const calcPosition = async () => {
  await nextTick();
  if (!wrapperRef.value || !tooltipRef.value) return;

  const trigger = wrapperRef.value.getBoundingClientRect();
  const tip = tooltipRef.value.getBoundingClientRect();
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const MARGIN = 8; // min distance from viewport edge

  let place = props.placement;
  let top, left;

  // Auto-flip if not enough space
  if (place === "top" && trigger.top < tip.height + GAP + MARGIN) place = "bottom";
  if (place === "bottom" && trigger.bottom + tip.height + GAP + MARGIN > vh)
    place = "top";
  if (place === "left" && trigger.left < tip.width + GAP + MARGIN) place = "right";
  if (place === "right" && trigger.right + tip.width + GAP + MARGIN > vw) place = "left";

  resolvedPlacement.value = place;

  switch (place) {
    case "top":
      top = trigger.top - tip.height - GAP + window.scrollY;
      left = trigger.left + trigger.width / 2 - tip.width / 2;
      break;
    case "bottom":
      top = trigger.bottom + GAP + window.scrollY;
      left = trigger.left + trigger.width / 2 - tip.width / 2;
      break;
    case "left":
      top = trigger.top + trigger.height / 2 - tip.height / 2 + window.scrollY;
      left = trigger.left - tip.width - GAP;
      break;
    case "right":
      top = trigger.top + trigger.height / 2 - tip.height / 2 + window.scrollY;
      left = trigger.right + GAP;
      break;
  }

  // Clamp horizontally so tooltip never goes off-screen
  left = Math.max(MARGIN, Math.min(left, vw - tip.width - MARGIN));

  tooltipStyle.value = {
    top: `${top}px`,
    left: `${left}px`,
  };
};

/* ── Show / Hide ── */
let hideTimer = null;

const show = async () => {
  clearTimeout(hideTimer);
  visible.value = true;
  await calcPosition();
};
const hide = (delay = 120) => {
  hideTimer = setTimeout(() => (visible.value = false), delay);
};
const toggle = () => (visible.value ? hideNow() : show());
const hideNow = () => {
  visible.value = false;
};

/* ── Outside click ── */
const onOutside = (e) => {
  if (!wrapperRef.value?.contains(e.target) && !tooltipRef.value?.contains(e.target)) {
    hideNow();
    document.removeEventListener("click", onOutside, true);
  }
};
const openClick = () => {
  if (visible.value) {
    hideNow();
    document.removeEventListener("click", onOutside, true);
  } else {
    show();
    document.addEventListener("click", onOutside, true);
  }
};

onBeforeUnmount(() => {
  clearTimeout(hideTimer);
  document.removeEventListener("click", onOutside, true);
});

/* ── Listener map ── */
const triggerListeners = computed(() => {
  switch (props.trigger) {
    case "click":
      return { onClick: openClick };

    case "both":
      return {
        onMouseenter: show,
        onMouseleave: () => hide(),
        onFocusin: show,
        onFocusout: () => hide(),
        onClick: openClick,
        onTouchstart: (e) => {
          e.preventDefault();
          openClick();
        },
      };

    default:
      // hover
      return {
        onMouseenter: show,
        onMouseleave: () => hide(),
        onFocusin: show,
        onFocusout: () => hide(),
        onTouchstart: (e) => {
          e.preventDefault();
          toggle();
        }, // touch fallback
      };
  }
});
</script>

<style>
/* ── NOT scoped — teleported outside component DOM ── */

.tooltip-wrap {
  position: relative;
  display: inline-flex;
}

/* ── Box ── */
.tt-box {
  position: fixed; /* fixed so scrollY doesn't matter for viewport clamping */
  position: absolute; /* overridden inline via top/left with scrollY added */
  z-index: 99999;
  min-width: 200px;
  max-width: min(280px, calc(100vw - 24px)); /* never wider than viewport */
  width: max-content;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 8px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.13), 0 1px 4px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  pointer-events: none;
}

/* ── Arrow ── */
.tt-arrow {
  position: absolute;
  width: 8px;
  height: 8px;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  transform: rotate(45deg);
}
.tt-box.top .tt-arrow {
  bottom: -5px;
  left: 50%;
  margin-left: -4px;
  border-top: none;
  border-left: none;
}
.tt-box.bottom .tt-arrow {
  top: -5px;
  left: 50%;
  margin-left: -4px;
  border-bottom: none;
  border-right: none;
}
.tt-box.left .tt-arrow {
  right: -5px;
  top: 50%;
  margin-top: -4px;
  border-left: none;
  border-bottom: none;
}
.tt-box.right .tt-arrow {
  left: -5px;
  top: 50%;
  margin-top: -4px;
  border-right: none;
  border-top: none;
}

/* ── Content ── */
.tt-title {
  background: linear-gradient(135deg, #0066cc, #0099ff);
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  padding: 6px 10px;
  letter-spacing: 0.15px;
  line-height: 1.3;
}
.tt-msg {
  padding: 7px 10px;
  font-size: 11.5px;
  color: #444;
  line-height: 1.5;
  margin: 0;
  white-space: normal;
  word-break: break-word;
}

/* ── Transitions ── */
.tt-top-enter-active,
.tt-top-leave-active,
.tt-bottom-enter-active,
.tt-bottom-leave-active,
.tt-left-enter-active,
.tt-left-leave-active,
.tt-right-enter-active,
.tt-right-leave-active {
  transition: opacity 0.14s ease, transform 0.14s ease;
}
.tt-top-enter-from,
.tt-top-leave-to {
  opacity: 0;
  transform: translateY(4px);
}
.tt-bottom-enter-from,
.tt-bottom-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
.tt-left-enter-from,
.tt-left-leave-to {
  opacity: 0;
  transform: translateX(4px);
}
.tt-right-enter-from,
.tt-right-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}
</style>
