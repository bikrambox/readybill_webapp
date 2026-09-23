<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-left">
        <img src="@/assets/images/register.png" alt="Registration" class="auth-image" />
        <div class="auth-overlay">
          <h3>{{ $t('common.world_s_no_1_e_selling') }}</h3>
        </div>
      </div>
      <div class="auth-right">
        <div class="auth-content">
          <img src="/logo.png" alt="Beauty+" class="logo" />
          <h2 class="auth-title">{{ $t('common.Register your account') }}</h2>
          <p class="auth-subtitle">{{ $t('common.join_beauty_plus') }}</p>
          
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <input 
                type="text" 
                class="form-control" 
                :placeholder="t('common.Enter your mobile number')"
                v-model="mobile"
                required
                autocomplete="off"
              />
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
              {{ $t('common.Continue') }}
            </button>
          </form>
          
          <div class="auth-footer">
            <p>{{$t('common.Already have an account')}}? <a href="/login">{{ $t('common.Sign In') }}</a></p>
            <p class="terms">{{ $t('common.By continuing, you agree to') }} <a href="#">{{ $t('common.Terms & Conditions') }}</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// import { ref } from 'vue'

// const mobile = ref('')
// const emit = defineEmits(['next-step'])

// const handleSubmit = () => {
//   emit('next-step', { mobile: mobile.value, step: 2 })
// }
</script>

<style scoped>

.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
  padding: 20px;
}

.auth-card {
  display: flex;
  width: 100%;
  max-width: 1000px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.auth-left {
  flex: 1;
  position: relative;
  min-height: 500px;
  display: none;
}

.auth-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.auth-overlay {
  position: absolute;
  bottom: 30px;
  left: 30px;
  color: white;
}

.auth-overlay h3 {
  font-size: 24px;
  font-weight: 600;
  max-width: 300px;
  line-height: 1.4;
}

.auth-right {
  flex: 1;
  padding: 60px 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-content {
  width: 100%;
  max-width: 400px;
}

.logo {
  height: 35px;
  margin-bottom: 30px;
}

.auth-title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 10px;
  color: #1a1a1a;
}

.auth-subtitle {
  font-size: 14px;
  color: #666;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-control {
  height: 50px;
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 12px 16px;
  font-size: 15px;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.btn-primary {
  height: 50px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  background-color: #007bff;
  border: none;
}

.btn-block {
  width: 100%;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.auth-footer {
  margin-top: 30px;
  text-align: center;
}

.auth-footer p {
  font-size: 14px;
  color: #666;
  margin-bottom: 10px;
}

.auth-footer a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.auth-footer a:hover {
  text-decoration: underline;
}

.terms {
  font-size: 12px;
  color: #999;
}

@media (min-width: 768px) {
  .auth-left {
    display: block;
  }
}

@media (max-width: 767px) {
  .auth-container {
    padding: 0;
    background-color: white;
  }
  
  .auth-card {
    box-shadow: none;
    border-radius: 0;
  }
  
  .auth-right {
    padding: 40px 20px;
  }
  
  .auth-title {
    font-size: 24px;
  }
}
</style>
