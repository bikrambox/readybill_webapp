<template>
  <div class="cp-card">
    <div class="cp-success-icon">
      <i class="bi bi-check-lg"></i>
    </div>
    <h6 class="cp-card-title mt-3 mb-1">Password Updated!</h6>
    <p class="cp-card-sub mb-4">
      Your account password has been changed successfully. You can now log in with your
      new password.
    </p>
    <div class="cp-alert cp-alert-info mb-4">
      <i class="bi bi-info-circle"></i>
      <span>For security, you may be logged out of other active sessions.</span>
    </div>
    <router-link to="/dashboard" class="cp-btn cp-btn-primary">
      <i class="bi bi-house me-2"></i>Go to Dashboard
    </router-link>
    <router-link to="/profile" class="cp-btn cp-btn-outline mt-2">
      <i class="bi bi-person me-2"></i>Back to Profile
    </router-link>
  </div>
</template>

<script setup></script>

<style scoped>
.cp-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}
.cp-card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a2e;
}
.cp-card-sub {
  font-size: 0.8rem;
  color: #6c757d;
  line-height: 1.5;
}
.cp-alert {
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.8rem;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  line-height: 1.5;
}
.cp-alert-info {
  background: #f0f4ff;
  border: 1px solid #d0d9f7;
  color: #3730a3;
}
.cp-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.18s;
}
.cp-btn-primary {
  background: #0d6efd;
  color: #fff;
}
.cp-btn-primary:hover {
  background: #0b5ed7;
  color: #fff;
}
.cp-btn-outline {
  background: transparent;
  color: #0d6efd;
  border: 1.5px solid #0d6efd;
}
.cp-btn-outline:hover {
  background: #e8f0fe;
}
.cp-success-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #d1fae5;
  color: #198754;
  font-size: 1.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: cp-pop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes cp-pop {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
.mt-2 {
  margin-top: 8px;
}
@media (max-width: 480px) {
  .cp-card {
    padding: 20px 16px;
    border-radius: 10px;
  }
}
</style>
