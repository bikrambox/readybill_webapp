<script setup>
import { ref } from "vue";

const handleSave = () => {
  alert("Terms accepted!");
};
</script>

<template>
  <div class="terms-container">
    <div class="container-fluid py-4">
      <!-- Title -->
      <div class="mb-4">
        <h2 class="fw-bold mb-0">Terms of Use</h2>
      </div>

      <!-- Terms Content Card -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
          <!-- Welcome Section -->
          <div class="terms-section mb-4">
            <p class="text-muted mb-0">
              Welcome to ReadyBill. By accessing or using our services, whether
              through our website, mobile application, or any other platform,
              you agree to be legally bound by the following Terms and
              Conditions. Please take a few minutes to read them carefully
              before proceeding, as they outline the rules and obligations that
              apply to your use of our services.
            </p>
          </div>

          <!-- Acceptance of Terms -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Acceptance of Terms</h5>
            <p class="text-muted mb-0">
              By using our services, you confirm that you have read, understood,
              and agreed to comply with these Terms, as well as all applicable
              local, national, and international laws and regulations. If you do
              not agree to any part of these Terms, you must not access or use
              our services. Continued use of the platform will be considered as
              your acceptance of any changes made to these Terms over time.
            </p>
          </div>

          <!-- Use of Service -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Use of Service</h5>
            <p class="text-muted mb-0">
              ReadyBill is to be used for lawful purposes only. Any misuse,
              manipulation, or unauthorized access to our systems, data, or
              services is strictly prohibited. You must not engage in activities
              that could harm, disable, or overburden our infrastructure or
              interfere with other users' access and experience. We reserve the
              right to investigate and take appropriate legal action against any
              violations.
            </p>
          </div>

          <!-- User Accounts -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">User Accounts</h5>
            <p class="text-muted mb-0">
              Some features of our platform require you to create a user
              account. You are solely responsible for maintaining the
              confidentiality of your login information and all activities that
              occur under your account. You must notify us immediately if you
              suspect any unauthorized use of your account. We are not
              responsible for any loss or damage arising from your failure to
              safeguard your account credentials.
            </p>
          </div>

          <!-- Intellectual Property -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Intellectual Property</h5>
            <p class="text-muted mb-0">
              All intellectual property on the ReadyBill platform—including but
              not limited to software code, interface design, visual content,
              logos, product names, and written content—are the exclusive
              property of ReadyBill or its content providers. These materials
              are protected by copyright, patent, trademark, and trademarks, and
              other intellectual property laws. Any unauthorized use,
              reproduction, or redistribution of the content is strictly
              prohibited and may result in legal consequences. Users are granted
              a limited, non-transferable license to access and use the platform
              solely for its intended business or personal purposes. This
              license does not include any right to modify, copy, distribute,
              transmit, display, perform, reproduce, publish, license, or create
              derivative works from any content on the platform without prior
              written permission from ReadyBill. Reverse engineering or
              attempting to extract the source code of any part of the
              application is expressly forbidden.
            </p>
          </div>

          <!-- Termination -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Termination</h5>
            <p class="text-muted mb-0">
              We reserve the right to suspend, restrict, or terminate your
              access to our services at our sole discretion, without prior
              notice, especially in cases of breach of these Terms, suspected
              fraudulent activity, or misuse of the platform. In the event of
              termination, any data associated with your account may be removed
              or archived, and we are not obligated to provide access to it
              thereafter.
            </p>
          </div>

          <!-- Limitation of Liability -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Limitation of Liability</h5>
            <p class="text-muted mb-0">
              While we strive to ensure a smooth and secure experience,
              ReadyBill will not be held liable for any direct, indirect,
              incidental, or consequential damages that may arise from your
              use—or inability to use—our services. This includes, but is not
              limited to, data loss, system failures, or business interruptions,
              regardless of whether we were informed of such potential damages
              in advance.
            </p>
          </div>

          <!-- Changes to Terms -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Changes to Terms</h5>
            <p class="text-muted mb-0">
              We reserve the right to update these Terms from time to time to
              reflect changes in our services, user feedback, or applicable
              laws. When we do, we will post the updated Terms on this page and
              update the effective date. It is your responsibility to review the
              Terms regularly. Continued use of our services following any
              changes constitutes your acceptance of the revised Terms.
            </p>
          </div>

          <!-- Third-Party Services -->
          <div class="terms-section mb-4">
            <h5 class="section-title mb-3">Third-Party Services</h5>
            <p class="text-muted mb-0">
              Our platform may contain links to third-party websites or services
              that are not owned or controlled by ReadyBill. We do not assume
              any responsibility for the content, policies, or practices of
              these external platforms. Your interactions with third-party
              services are governed solely by their terms and policies. We
              encourage you to review their terms before engaging with them, as
              your use of such services is at your own risk.
            </p>
          </div>

          <!-- Content Submissions -->
          <div class="terms-section">
            <h5 class="section-title mb-3">Content Submissions</h5>
            <p class="text-muted mb-0">
              By submitting any content, including but not limited to text,
              images, or feedback, to our platform, you grant us a
              non-exclusive, worldwide, royalty-free, perpetual license to use,
              modify, reproduce, and distribute such content for the purpose of
              providing and improving our services. You represent and warrant
              that you have the right to third-party content and that it does
              not violate the rights of any third party or applicable law.
            </p>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</template>

<style scoped>
.terms-container {
  /* max-width: 800px; */
  margin: 0 auto;
}

.terms-section {
  line-height: 1.7;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #212529;
}

.save-btn {
  background-color: #5ebe9b;
  border-color: #5ebe9b;
  padding: 0.5rem 2rem;
  font-weight: 500;
  cursor: pointer;
}

.save-btn:hover {
  background-color: #4da885;
  border-color: #4da885;
}

/* Desktop specific */
@media (min-width: 768px) {
  .save-btn {
    width: auto;
  }
}

/* Mobile specific */
@media (max-width: 767.98px) {
  .terms-container {
    max-width: 100%;
  }

  .save-btn {
    width: 100%;
  }
}

@media (max-width: 991.98px) {
  .terms-container {
    max-width: 100%;
  }
}
</style>
