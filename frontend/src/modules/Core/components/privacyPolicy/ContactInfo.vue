<template>
  <div class="contact-info mt-3">
    <div class="mb-2">
      <strong>Email:</strong>
      <a href="mailto:support@readybill.app" class="ms-2">
        support@readybill.app
      </a>
    </div>
    <div class="address-section">
      <strong>Address:</strong>
      <address class="d-inline-block ms-2 mb-0">
        Old UCO Bank Bldg.,<br />
        Adabari Tiniali,<br />
        Guwahati - 781012, India.
      </address>
    </div>
  </div>
</template>

<script setup>
// Static contact information component
// Can be made dynamic by accepting props if needed
</script>

<style scoped>
.contact-info {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
  border-radius: 0.5rem;
  border-left: 4px solid #0d6efd;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.contact-info a {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.contact-info a:hover {
  color: #0a58ca;
  text-decoration: underline;
}

.contact-info strong {
  color: #2c3e50;
  font-weight: 600;
  font-size: 0.95rem;
}

address {
  font-style: normal;
  line-height: 1.7;
  color: #495057;
}

.address-section {
  display: flex;
  align-items: flex-start;
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .contact-info {
    padding: 1rem;
  }

  .contact-info > div {
    display: block !important;
    margin-bottom: 1rem;
  }

  .contact-info > div:last-child {
    margin-bottom: 0;
  }

  .contact-info .ms-2 {
    margin-left: 0 !important;
    display: block;
    margin-top: 0.5rem;
  }

  .address-section {
    flex-direction: column;
  }
}
</style>
