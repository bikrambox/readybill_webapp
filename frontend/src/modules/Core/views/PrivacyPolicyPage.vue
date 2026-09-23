<script setup>
import { useI18n } from 'vue-i18n'
import '../assets/css/home/mobile-app.css'
import '../assets/css/home/footer.css'

const { t } = useI18n()

// Clean text function
const ct = (key) => {
  let text = t(key).replace(/\s+/g, ' ').trim()
  return text
}
</script>

<template>
  <div class="container mt-5 paragraphSection">
    <!-- Breadcrumb -->
    <div class="row">
      <div class="col-12 mt-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb px-0">
            <li class="breadcrumb-item">
              <a href="/home">{{ ct('common.Home') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ ct('common.Privacy Policy') }}
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <div class="row">
      <div class="col-12">
        <!-- Title -->
        <h1>{{ ct('common.Privacy Policy') }}</h1>
        <p>{{ ct('privacy_policy_page.last_revision') }}</p>

        <!-- Introduction -->
        <p class="text-break" v-html="ct('privacy_policy_page.section1.para.1')"></p>
        <p class="text-break" v-html="ct('privacy_policy_page.section1.para.2')"></p>
        <p class="">
            <strong>Email ID:</strong>
            <a href="mailto:support@readybill.app" class="ms-2 text-decoration-none text-primary">
                support@readybill.app
            </a>
        </p>
        <p class="text-break" v-html="ct('privacy_policy_page.section1.para.3')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 1: Using Ready Bill as a Visitor -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">1. {{ ct('privacy_policy_page.section2.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section2.para.1')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 2: Email and Phone Usage -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">2. {{ ct('privacy_policy_page.section3.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section3.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section3.para.2')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 3: Permitted Disclosures -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">3. {{ ct('privacy_policy_page.section4.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section4.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section4.para.2')"></p>
        <p v-html="ct('privacy_policy_page.section4.para.3')"></p>
        <p v-html="ct('privacy_policy_page.section4.para.4')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 4: Access Your Information -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">4. {{ ct('privacy_policy_page.section5.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section5.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section5.para.2')"></p>
        
        <!-- Email Contact -->
        <div class="mt-3">
          <span class="fw-semibold">Email:</span>
          <a href="mailto:support@readybill.app" class="ms-2 text-decoration-none">
            support@readybill.app
          </a>
        </div>
      </div>
    </div>

    <hr />

    <!-- Section 5: Security Practices -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">5. {{ ct('privacy_policy_page.section6.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section6.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section6.para.2')"></p>
        <p v-html="ct('privacy_policy_page.section6.para.3')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 6: How Information is Transmitted -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">6. {{ ct('privacy_policy_page.section7.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section7.para.1')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 7: Legal Obligation -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">7. {{ ct('privacy_policy_page.section8.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section8.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section8.para.2')"></p>
      </div>
    </div>

    <hr />

    <!-- Section 8: Changes to Privacy Policy -->
    <div class="row">
      <div class="col-md-4">
        <h6 class="fw-bold">8. {{ ct('privacy_policy_page.section9.heading') }}</h6>
      </div>
      <div class="col-md-8 fw-normal">
        <p v-html="ct('privacy_policy_page.section9.para.1')"></p>
        <p v-html="ct('privacy_policy_page.section9.para.2')"></p>
      </div>
    </div>

    <div class="mb-5"></div>
  </div>
</template>

<style scoped>
/* Match Blade file styling */
.paragraphSection p,
.paragraphSection h6,
.paragraphSection li {
  font-size: 17px !important;
}

.paragraphSection p {
  max-width: 100%;
  word-wrap: break-word;
}

.paragraphSection ol li {
  margin-bottom: 10px !important;
}

/* Page Title */
.paragraphSection h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #2c3e50;
}

/* Section Headings */
.paragraphSection h6 {
  font-size: 1.1rem;
  color: #2c3e50;
  line-height: 1.5;
}

/* Horizontal Rules */
.paragraphSection hr {
  margin: 2rem 0;
  border-color: #dee2e6;
  opacity: 0.5;
}

/* Breadcrumb */
.breadcrumb-item a {
  color: #0d6efd;
  text-decoration: none;
}

.breadcrumb-item a:hover {
  text-decoration: underline;
}

/* Email Link */
.paragraphSection a {
  color: #0d6efd;
  font-weight: 500;
}

.paragraphSection a:hover {
  text-decoration: underline;
}

/* Paragraph Spacing */
.paragraphSection p {
  margin-bottom: 1rem;
  line-height: 1.7;
  color: #495057;
}

/* Lists */
.paragraphSection ol,
.paragraphSection ul {
  padding-left: 2rem;
  margin-bottom: 1rem;
}

.paragraphSection li {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

/* Two Column Layout */
.row {
  margin-bottom: 1rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .paragraphSection p,
  .paragraphSection h6,
  .paragraphSection li {
    font-size: 15px !important;
  }

  .paragraphSection h1 {
    font-size: 1.5rem;
  }

  .paragraphSection hr {
    margin: 1.5rem 0;
  }

  .col-md-4,
  .col-md-8 {
    padding-left: 15px;
    padding-right: 15px;
  }

  .col-md-4 h6 {
    margin-bottom: 1rem;
  }
}

@media (max-width: 576px) {
  .paragraphSection {
    padding: 0 1rem;
  }

  .paragraphSection h1 {
    font-size: 1.3rem;
  }

  .mt-5 {
    margin-top: 2rem !important;
  }
}

/* Print Styles */
@media print {
  .breadcrumb {
    display: none;
  }

  .paragraphSection p,
  .paragraphSection h6,
  .paragraphSection li {
    font-size: 12pt !important;
  }

  .paragraphSection hr {
    border-color: #000;
  }
}
</style>
