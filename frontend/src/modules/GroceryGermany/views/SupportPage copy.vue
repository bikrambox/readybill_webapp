<template>
  <div class="container-fluid">
    <!-- Page Title -->
    <div class="pagetitle">
      <h1>{{ $t("common.Support") }}</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="sell">{{ $t("common.Home") }}</a>
          </li>
          <li class="breadcrumb-item active">{{ $t("common.Support") }}</li>
        </ol>
      </nav>
    </div>

    <!-- Profile Section -->
    <section class="section profile">
      <div class="row">
        <div class="col-xl-8">
          <div class="card shadow-sm">
            <div class="card-body pt-4 px-4">
              <div class="row mb-4 align-items-center">
                <div class="col-md-5 mb-3 mb-md-0">
                  <button
                    type="button"
                    class="btn btn-info w-100"
                    @click="openAskQuestionModal"
                  >
                    {{ t("support_page.ask_your_questions") }}
                  </button>
                </div>
                <div class="col-md-7 text-md-end">
                  <p class="mb-0 fs-5 text-secondary fw-medium">
                    {{ t("support_page.contact_n_sale_support") }}
                  </p>
                </div>
              </div>

              <Accordion :items="faqItems" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <AskYourQuestionModal ref="askQuestionModalRef" />
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import Accordion from "@/modules/GroceryIndia/components/support/Accordion.vue"; // adjust paths as needed
import AskYourQuestionModal from "@/modules/GroceryIndia/components/support/AskYourQuestionsModal.vue";

const { t } = useI18n();

const faqItems = [
  {
    question: t("support_page.faq1.quest"),
    answer: `
      <p>${t("support_page.faq1.para.1")}</p>
      <p>${t("support_page.faq1.para.2")}</p>
      <p>${t("support_page.faq1.para.3")}</p>
      <p>${t("support_page.faq1.para.4")}</p>
      <p>${t("support_page.faq1.para.5")}</p>
      <p>${t("support_page.faq1.para.6")}</p>
      <p>${t("support_page.faq1.para.7")}</p>
      <p>${t("support_page.faq1.para.8")}</p>
      <p>${t("support_page.faq1.para.9")}</p>
      <p>${t("support_page.faq1.para.10")}</p>
      <p>${t("support_page.faq1.para.11")}</p>
    `,
  },
  // Add faq2 to faq11 similarly, using the same pattern...
  {
    question: t("support_page.faq2.quest"),
    answer: `
      <p>${t("support_page.faq2.para.1")}</p>
      <p>${t("support_page.faq2.para.2")}</p>
      <p>${t("support_page.faq2.para.3")}</p>
      <p>${t("support_page.faq2.para.4")}</p>
      <p>${t("support_page.faq2.para.5")}</p>
      <p>${t("support_page.faq2.para.6")}</p>
    `,
  },
  // ... faq3 to faq11 short examples below
  {
    question: t("support_page.faq3.quest"),
    answer: `<p>${t("support_page.faq3.para.1")}</p>`,
  },
  {
    question: t("support_page.faq4.quest"),
    answer: `<p>${t("support_page.faq4.para.1")}</p><p>${t(
      "support_page.faq4.para.2"
    )}</p>`,
  },
  {
    question: t("support_page.faq5.quest"),
    answer: `<p>${t("support_page.faq5.para.1")}</p>`,
  },
  {
    question: t("support_page.faq6.quest"),
    answer: `<p>${t("support_page.faq6.para.1")}</p>`,
  },
  {
    question: t("support_page.faq7.quest"),
    answer: `<p>${t("support_page.faq7.para.1")}</p><ol class="ps-4"><li>${t(
      "support_page.faq7.para.2"
    )}</li><li>${t("support_page.faq7.para.3")}</li><li>${t(
      "support_page.faq7.para.4"
    )}</li><li>${t("support_page.faq7.para.5")}</li><li>${t(
      "support_page.faq7.para.6"
    )}</li></ol>`,
  },
  {
    question: t("support_page.faq8.quest"),
    answer: `<p>${t("support_page.faq8.para.1")}</p>`,
  },
  {
    question: t("support_page.faq9.quest"),
    answer: `<p>${t("support_page.faq9.para.1")}</p>`,
  },
  {
    question: t("support_page.faq10.quest"),
    answer: `<p>${t("support_page.faq10.para.1")}</p>`,
  },
  {
    question: t("support_page.faq11.quest"),
    answer: `<p>${t("support_page.faq11.para.1")}</p><p>${t(
      "support_page.faq11.para.2"
    )}</p><p>${t("support_page.faq11.para.3")}</p><p>${t(
      "support_page.faq11.para.4"
    )}</p>`,
  },
];

const askQuestionModalRef = ref(null);

function openAskQuestionModal() {
  askQuestionModalRef.value?.open();
}
</script>


<style scoped>
.pagetitle h1 {
  font-size: 1.9rem;
}
.pagetitle h6 {
  color: #012970;
  font-size: 1.1rem;
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

@media (max-width: 768px) {
  .section.profile {
    padding: 1rem;
  }
}
</style>
