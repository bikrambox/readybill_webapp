<script setup>
import { useI18n } from 'vue-i18n'
import '../assets/css/home/mobile-app.css'
import '../assets/css/home/footer.css'

const { t } = useI18n()

// Clean text function to handle multiline strings
const ct = (key, params = {}) => {
  let text = t(key, params).replace(/\s+/g, ' ').trim()
  return text
}
</script>

<template>
  <div class="container paragraphSection">
    <div class="row">
      <!-- Breadcrumb -->
      <div class="col-12 mt-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb px-0">
            <li class="breadcrumb-item">
              <a href="/home">{{ ct('common.Home') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ ct('terms_n_conditions_page.title') }}
            </li>
          </ol>
        </nav>
      </div>

      <div class="col-12">
        <!-- Page Title -->
        <div class="row mb-4">
          <div class="col-12">
            <h3>{{ ct('terms_n_conditions_page.title') }}</h3>
          </div>
        </div>

        <!-- Section 1 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section1.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section1.para.1')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section1.para.2')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section1.para.3')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 2 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section2.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section2.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 3 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section3.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section3.para.1', { subscription_url: '/subscription' })"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section3.para.2')"></p>
            <ol>
              <li v-html="ct('terms_n_conditions_page.section3.para.3')"></li>
              <li v-html="ct('terms_n_conditions_page.section3.para.4', { contact_url: '/contact' })"></li>
              <li v-html="ct('terms_n_conditions_page.section3.para.5')"></li>
              <li v-html="ct('terms_n_conditions_page.section3.para.6')"></li>
            </ol>
          </div>
        </div>
        <hr />

        <!-- Section 4 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section4.heading')"></h2>
            <ol>
              <li>
                <span v-html="ct('terms_n_conditions_page.section4.para.1')"></span>
                <br /><br />
                <span v-html="ct('terms_n_conditions_page.section4.para.2')"></span>

                <ul style="list-style-type: lower-alpha; padding-left: 20px;">
                  <li v-html="ct('terms_n_conditions_page.section4.para.3')"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.4', { contact_url: '/contact' })"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.5')"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.6')"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.7')"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.8')"></li>
                  <li>
                    <span v-html="ct('terms_n_conditions_page.section4.para.9')"></span>
                    <br /><br />
                    <span v-html="ct('terms_n_conditions_page.section4.para.10')"></span>
                    <br /><br />
                    <span v-html="ct('terms_n_conditions_page.section4.para.11')"></span>
                  </li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.12')"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.13', { agent_url: '/agents' })"></li>
                  <li v-html="ct('terms_n_conditions_page.section4.para.14')"></li>
                </ul>
              </li>
              <li>
                <span v-html="ct('terms_n_conditions_page.section4.para.15')"></span>
                <br /><br />
                <span v-html="ct('terms_n_conditions_page.section4.para.16')"></span>
              </li>
            </ol>
          </div>
        </div>
        <hr />

        <!-- Section 5 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section5.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section5.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 6 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section6.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section6.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 7 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section7.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section7.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 8 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section8.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section8.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 9 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section9.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section9.para.1')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section9.para.2')"></p>
            <p v-html="ct('terms_n_conditions_page.section9.para.3')"></p>
            <p v-html="ct('terms_n_conditions_page.section9.para.4')"></p>
            <p v-html="ct('terms_n_conditions_page.section9.para.5')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 10 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section10.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.1')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.2')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.3')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.4')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.5')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.6')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.7')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.8')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.9')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.10')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.11')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.12')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.13')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.14')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.15')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.16')"></p>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section10.para.17')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 11 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section11.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section11.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 12 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section12.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section12.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 13 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section13.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section13.para.1')"></p>
            <ol>
              <li v-html="ct('terms_n_conditions_page.section13.para.2')"></li>
              <li v-html="ct('terms_n_conditions_page.section13.para.3')"></li>
              <li v-html="ct('terms_n_conditions_page.section13.para.4')"></li>
            </ol>
            <p v-html="ct('terms_n_conditions_page.section13.para.5')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.6')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.7')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.8')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.9')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.10')"></p>
            <p v-html="ct('terms_n_conditions_page.section13.para.11')"></p>
            <ol>
              <li v-html="ct('terms_n_conditions_page.section13.para.12')"></li>
              <li v-html="ct('terms_n_conditions_page.section13.para.13')"></li>
              <li v-html="ct('terms_n_conditions_page.section13.para.14')"></li>
            </ol>
            <p v-html="ct('terms_n_conditions_page.section13.para.15')"></p>
            <ol>
              <li v-html="ct('terms_n_conditions_page.section13.para.16')"></li>
              <li v-html="ct('terms_n_conditions_page.section13.para.17')"></li>
            </ol>
            <p v-html="ct('terms_n_conditions_page.section13.para.18')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 14 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section14.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section14.para.1')"></p>
            <p v-html="ct('terms_n_conditions_page.section14.para.2')"></p>
            <p v-html="ct('terms_n_conditions_page.section14.para.3')"></p>
            <p v-html="ct('terms_n_conditions_page.section14.para.4')"></p>
            <p v-html="ct('terms_n_conditions_page.section14.para.5')"></p>
            <p v-html="ct('terms_n_conditions_page.section14.para.6')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 15 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section15.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section15.para.1')"></p>
            <p v-html="ct('terms_n_conditions_page.section15.para.2')"></p>
            <p v-html="ct('terms_n_conditions_page.section15.para.3')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 16 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section16.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section16.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 17 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section17.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section17.para.1')"></p>
          </div>
        </div>

        <!-- Section 18 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section18.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section18.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 19 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section19.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section19.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 20 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section20.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section20.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 21 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section21.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section21.para.1')"></p>
          </div>
        </div>
        <hr />

        <!-- Section 22 -->
        <div class="row">
          <div class="col-12">
            <h2 v-html="ct('terms_n_conditions_page.section22.heading')"></h2>
            <p class="text-break" v-html="ct('terms_n_conditions_page.section22.para.1')"></p>
          </div>
        </div>
      </div>
    </div>
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
.paragraphSection h3 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

/* Section Headings */
.paragraphSection h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #2c3e50;
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

/* Links */
.paragraphSection :deep(a) {
  color: #0d6efd;
  font-weight: 500;
  text-decoration: none;
}

.paragraphSection :deep(a:hover) {
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

.paragraphSection ol ol,
.paragraphSection ol ul,
.paragraphSection ul ol,
.paragraphSection ul ul {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .paragraphSection p,
  .paragraphSection h6,
  .paragraphSection li {
    font-size: 15px !important;
  }

  .paragraphSection h3 {
    font-size: 1.5rem;
  }

  .paragraphSection h2 {
    font-size: 1.25rem;
  }

  .paragraphSection hr {
    margin: 1.5rem 0;
  }

  .col-12 {
    padding-left: 15px;
    padding-right: 15px;
  }
}

@media (max-width: 576px) {
  .paragraphSection {
    padding: 0 0.5rem;
  }

  .paragraphSection h3 {
    font-size: 1.3rem;
  }

  .paragraphSection h2 {
    font-size: 1.1rem;
  }

  .mt-4 {
    margin-top: 1rem !important;
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
