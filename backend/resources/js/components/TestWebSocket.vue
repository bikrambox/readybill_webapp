<template>
  <div class="container">
    <div class="card">
      <div class="card-header">WebSocket Test</div>
      <div class="card-body">
        <p>Listening for TestEvent on test-channel...</p>
        <div v-if="message" class="alert alert-success">
          Received message: {{ message }}
        </div>
        <button @click="triggerTestEvent" class="btn btn-primary">
          Trigger Test Event
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      message: null,
    };
  },
  mounted() {
    console.log("TestWebSocket component mounted");
    this.setupWebSocket();
  },
  methods: {
    setupWebSocket() {
      if (!window.Echo) {
        console.error("Laravel Echo not available");
        return;
      }
      console.log("Subscribing to test-channel");
      window.Echo.channel("test-channel")
        .listen("TestEvent", (data) => {
          console.log("TestEvent received:", JSON.stringify(data, null, 2));
          this.message = data.message;
        })
        .error((error) => {
          console.error("WebSocket channel error:", error);
        });
    },
    async triggerTestEvent() {
      try {
        const response = await axios.post(
          "/test-event",
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
            },
          }
        );
        console.log("Test event triggered:", response.data);
      } catch (error) {
        console.error("Failed to trigger test event:", error);
        alert("Failed to trigger test event");
      }
    },
  },
  beforeUnmount() {
    if (window.Echo) {
      window.Echo.leave("test-channel");
      console.log("Unsubscribed from test-channel");
    }
  },
};
</script>

<style scoped>
.container {
  margin-top: 20px;
}
.card {
  max-width: 600px;
  margin: 0 auto;
}
.alert {
  margin-top: 10px;
}
</style>