<template>
  <div class="container-fluid py-4">
    <!-- Page Title -->
    <div class="mb-4">
      <h1 class="h2 fw-bold">{{ $t("common.Support") }}</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ $t("common.Support") }}
          </li>
        </ol>
      </nav>
    </div>

    <!-- Support Section -->
    <section class="pb-4">
      <div class="row">
        <div class="col-xl-8 col-lg-10">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <!-- Action Row -->
              <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-5">
                  <button
                    type="button"
                    class="btn btn-primary w-100"
                    @click="openAskQuestionModal"
                  >
                    <i class="bi bi-question-circle me-2"></i>
                    {{ t("support_page.ask_your_questions") }}
                  </button>
                </div>
                <div class="col-md-7 text-md-end">
                  <p class="mb-0 fs-6 text-muted fw-medium">
                    {{ t("support_page.contact_n_sale_support") }}
                  </p>
                </div>
              </div>

              <!-- FAQ Accordion -->
              <div class="accordion" id="faqAccordion">
                <div
                  v-for="(item, index) in faqItems"
                  :key="index"
                  class="accordion-item border"
                >
                  <h2 class="accordion-header">
                    <button
                      class="accordion-button collapsed"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="`#collapse${index}`"
                      :aria-expanded="index === 0"
                      :aria-controls="`collapse${index}`"
                    >
                      {{ item.question }}
                    </button>
                  </h2>
                  <div
                    :id="`collapse${index}`"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion"
                  >
                    <div class="accordion-body" v-html="item.answer"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AskYourQuestionModal ref="askQuestionModalRef" />

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import AskYourQuestionModal from "@/modules/GroceryIndia/components/support/AskYourQuestionsModal.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

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
      <p>${t("support_page.faq1.para.12")}</p>
      <p>${t("support_page.faq1.para.13")}</p>
    `,
  },
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
