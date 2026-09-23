<template>
  <div class="container">
    <!-- Global overlay loader -->
    <div v-if="isLoading" class="overlay">
      <div class="overlay-inner">
        <div class="overlay-content">
          <div class="lds-spinner loader">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
          </div>
          <!-- <div class="mt-2 text-muted" style="font-size: 14px">Loading...</div> -->
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <ul class="nav nav-tabs">
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'unpaid' }"
                  @click="activeTab = 'unpaid'"
                  href="#"
                  >Unpaid</a
                >
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'paid' }"
                  @click="activeTab = 'paid'"
                  href="#"
                  >Paid</a
                >
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="row mt-4">
              <div class="col-12 text-end isAdminSection">
                <a class="btn btn-primary" href="generate-report" role="button"
                  >Generate Report</a
                >
              </div>
              <div
                class="col-md-4 col-sm-12 my-2 d-flex justify-content-md-start justify-content-center"
              >
                <label class="pt-1" for="entitySelect">Show</label>
                <select
                  v-model="pageLength"
                  id="entitySelect"
                  class="form-select mx-2"
                  style="width: 30%"
                >
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
                <label class="pt-1 totalTransactionRecords" for=""
                  >Entries</label
                >
              </div>
              <div class="col-md-4"></div>
              <div
                class="col-md-4 col-12 my-2 d-flex justify-content-md-end justify-content-center"
              >
                <select v-model="filterOption" id="filterSelect" class="mx-2">
                  <option value="invoice_number">Invoice Number</option>
                  <option value="date">Date</option>
                  <option value="user">User</option>
                  <option value="total">Total</option>
                </select>

                <!-- <input
                  v-model="searchValue"
                  type="text"
                  id="dataTableSearch"
                  class="form-control"
                  :placeholder="
                    filterOption === 'date' ? 'DD/MM/YYYY' : 'Search...'
                  "
                /> -->
                <input
                  v-if="filterOption !== 'date'"
                  v-model="searchValue"
                  type="text"
                  id="dataTableSearch"
                  class="form-control"
                  placeholder="Search..."
                />

                <!-- Date-only input with mask and validation -->
                <input
                  v-else
                  :value="searchValue"
                  type="text"
                  id="dataTableSearch"
                  class="form-control"
                  placeholder="DD/MM/YYYY"
                  inputmode="numeric"
                  maxlength="10"
                  @beforeinput="blockNonDigits"
                  @input="onDateMaskedInput"
                  @blur="onDateBlur"
                />
              </div>

              <div class="col-12 text-end">
                <small
                  v-if="
                    filterOption === 'date' &&
                    searchValue &&
                    !isValidDDMMYYYY(searchValue)
                  "
                  class="text-danger"
                  >Enter a valid date in DD/MM/YYYY.</small
                >
              </div>

              <div class="col-12 table-responsive">
                <DataTable
                  class="table table-responsive-md table-responsive-lg table-responsive-xl"
                  :columns="columns"
                  :options="options"
                  ref="transactionTable"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Transaction Details -->
    <div
      class="modal fade"
      id="transactionalDetails"
      ref="transactionalDetailsModal"
      tabindex="-1"
      aria-labelledby="transactionalDetailsLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="transactionalDetailsLabel">
              Item List
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="billing_id" class="form-label">Invoice Number</label>
              <input
                type="hidden"
                class="form-control"
                id="billing_id"
                ref="billingId"
                readonly
              />
            </div>
            <input
              type="text"
              ref="invoiceNumber"
              id="u_invoic_number"
              readonly
            />
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody ref="itemTableBody"></tbody>
            </table>
          </div>
          <div class="modal-footer d-flex justify-content-start">
            <button
              type="button"
              class="btn btn-danger"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary generateInvoice"
              @click="generateInvoice"
            >
              Print
            </button>

            <button
              type="button"
              class="btn btn-success sendWhatsApp"
              @click="sendToWhatsApp"
            >
              Send Invoice SMS
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Share Invoice Modal -->
    <div
      class="modal fade"
      id="shareInvoice"
      ref="shareInvoiceModal"
      tabindex="-1"
      aria-labelledby="shareInvoiceLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header text-center">
            <h5 class="modal-title" id="shareInvoiceLabel">
              Send Invoice via SMS
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <!-- Error Display for API Validation -->
            <div class="container mb-4 error-div" v-if="errorMessage">
              <div
                class="card border-danger"
                style="border-radius: 10px; overflow: hidden"
              >
                <div
                  class="card-header text-white fw-bold py-0"
                  style="
                    background-color: #b64a21;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                  "
                >
                  <h6 class="my-0">Error</h6>
                  <button
                    class="btn btn-sm text-white close-error-btn"
                    style="
                      background: transparent;
                      border: none;
                      font-size: 22px;
                    "
                  >
                    &times;
                  </button>
                </div>
                <div
                  class="card-body error-body text-danger"
                  style="background-color: #fdf2f1"
                >
                  {{ errorMessage }}
                </div>
              </div>
            </div>

            <!-- Invoice Number Display -->
            <div class="mb-3">
              <label class="form-label fw-bold">Invoice Number</label>
              <input
                type="text"
                class="form-control"
                :value="currentInvoiceNumber"
                readonly
                style="background-color: #f8f9fa"
              />
            </div>

            <!-- Mobile Number Input -->

            <div class="mb-4">
              <label for="mobile" class="form-label">Mobile Number</label>
              <div class="input-group custom-input-group">
                <div class="custom-select-wrapper">
                  <select
                    class="form-select country-code-select1"
                    id=""
                    v-model="countryCode"
                  >
                    <option
                      v-for="country in countries"
                      :key="country.code"
                      :value="country.code"
                    >
                      {{ country.flag }} ({{ country.dial_code }})
                    </option>
                  </select>
                </div>

                <input
                  type="tel"
                  class="form-control"
                  id="phone_number"
                  v-model="phoneNumber"
                  placeholder="Enter mobile number"
                  :maxlength="11"
                  @input="formatPhoneNumber"
                  :class="{
                    'is-invalid':
                      !!errorMessage || (phoneNumber && !isValidPhoneNumber()),
                    'is-valid':
                      !errorMessage && phoneNumber && isValidPhoneNumber(),
                  }"
                  style="border-left: none"
                  autofocus
                />
              </div>
            </div>

            <!-- Loading indicator if needed -->
            <div v-if="isSendingSMS" class="text-center">
              <div
                class="spinner-border spinner-border-sm text-primary"
                role="status"
              >
                <span class="visually-hidden">Sending...</span>
              </div>
              <p class="mt-2 mb-0">Sending SMS...</p>
            </div>
          </div>
          <div class="text-center mb-3">
            <button
              type="button"
              class="btn btn-success mx-2"
              @click="sendSMS"
              :disabled="!phoneNumber || !isValidPhoneNumber() || isSendingSMS"
            >
              <span
                v-if="isSendingSMS"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              Send SMS
            </button>

            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
              :disabled="isSendingSMS"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Share Invoice Modal -->
  </div>
</template>

<script>
import axios from "axios";
import DataTable from "datatables.net-vue3";
import DataTablesLib from "datatables.net-bs5";
import _ from "lodash";
import bellSound from "@/assets/sounds/bell.mp3";

DataTable.use(DataTablesLib);

export default {
  components: { DataTable },
  data() {
    return {
      isLoading: true,
      shopId: null,
      userId: null,
      isAdmin: null,
      bellAudio: new Audio(bellSound),
      playCount: 0,
      maxPlays: 3,
      pageLength: 10,
      filterOption: "invoice_number",
      searchValue: "",
      activeTab: "unpaid",

      countryCode: this.userCountryCode || "DE",
      phoneNumber: "",
      countries: [],
      currentInvoiceNumber: "",
      errorMessage: "",

      columns: [
        { data: "invoice_number", title: "Invoice #" },
        {
          data: null,
          title: "Products",
          render: (data) => {
            const itemList = JSON.parse(data.item_list);
            return itemList
              .map(
                (item) =>
                  `${item.itemName} ${item.quantity} ${item.selectedUnit}`
              )
              .join(", ");
          },
        },
        {
          data: null,
          title: "Total",
          render: (data) =>
            data.total_price < 0
              ? `− ${currency(-1 * data.total_price)}`
              : currency(data.total_price),
        },
        { data: "user_name", title: "User" },
        {
          data: null,
          title: "Date",
          render: (data) =>
            new Date(data.created_at).toLocaleString("en-US", {
              year: "numeric",
              month: "long",
              day: "numeric",
              hour: "2-digit",
              minute: "2-digit",
              hour12: true,
            }),
        },
        {
          data: null,
          title: "Action",
          orderable: false,
          render: (data) => {
            if (this.activeTab === "unpaid") {
              return `<button class="btn btn-sm btn-success mark-paid" data-id="${data.id}">Mark as Paid</button>`;
            }
            return "";
          },
          visible: this.activeTab === "unpaid" && this.isAdmin == 1,
        },
      ],
      options: {
        serverSide: true,
        processing: true,
        searching: false,
        lengthChange: false,
        rowId: "id",
        ajax: {
          url: `${import.meta.env.VITE_API_URL}${
            import.meta.env.VITE_GROCERY_GERMANY_PREFIX
          }all-transactions`,
          type: "POST",
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
            "auth-key": `${localStorage.getItem("api_key") || ""}`,
            "X-API-Secret": `${localStorage.getItem("x_api_key_secret") || ""}`,
          },
          data: (d) => {
            return {
              ...d,
              filter_option: this.filterOption,
              search: {
                value: this.searchValue,
              },
              payment_status: this.activeTab === "unpaid" ? 0 : 1,
            };
          },
          beforeSend: () => {
            this.isLoading = true;
          },
          dataSrc: (json) => {
            this.totalRecords = json.recordsFiltered;
            console.log(
              "DataTable data received:",
              json.data.length,
              "records"
            );
            return json.data || [];
          },
          error: (xhr) => {
            console.error("AJAX error:", xhr.status, xhr.responseText);
          },
          complete: () => {
            // Ensure overlay hides even if error occurs
            this.isLoading = false;
          },
        },
        pageLength: 10,
        order: [[4, "desc"]],
        language: {
          emptyTable: "Currently there are no transactions",
        },
        drawCallback: (settings) => {
          console.log("DataTable draw completed");
          const api = this.$refs.transactionTable.dt;
          let lastMonthYear = null;
          const monthlyTotals = {};

          api
            .rows({ page: "current" })
            .data()
            .each((data) => {
              const currentMonth = new Date(data.created_at).getMonth() + 1;
              const currentYear = new Date(data.created_at).getFullYear();
              const currentMonthYear = `${currentMonth}-${currentYear}`;

              if (!monthlyTotals[currentMonthYear]) {
                monthlyTotals[currentMonthYear] = 0;
              }
              monthlyTotals[currentMonthYear] += parseFloat(data.total_price);
            });

          api
            .rows({ page: "current" })
            .nodes()
            .each((row, i) => {
              const data = api.row(i).data();
              const currentMonth = new Date(data.created_at).getMonth() + 1;
              const currentYear = new Date(data.created_at).getFullYear();
              const currentMonthYear = `${currentMonth}-${currentYear}`;

              if (lastMonthYear !== currentMonthYear) {
                const monthName = new Date(
                  2024,
                  currentMonth - 1,
                  1
                ).toLocaleString("default", { month: "long" });
                const total = monthlyTotals[currentMonthYear].toFixed(2);
                const totalDisplay =
                  total < 0 ? `− ${currency(-1 * total)}` : currency(total);
                $(row).before(
                  `<tr class="month-header fw-bold text-start table-active"><td colspan="1">${monthName}, ${currentYear}</td><td colspan="${
                    this.activeTab === "unpaid" ? 3 : 2
                  }"></td><td class="text-center" colspan="1">${totalDisplay}</td></tr>`
                );
                lastMonthYear = currentMonthYear;
              }
            });

          this.isLoading = false;
        },
        createdRow: (row, data, dataIndex) => {
          console.log(`Creating row for transaction ID: ${data.id}`);
          $(row).css("cursor", "pointer");

          // Apply highlight and transition for new/updated rows
          if (this.isNewTransaction(data.id)) {
            $(row).addClass("highlight-new-row");
            setTimeout(() => {
              $(row).addClass("transition-new-row");
              console.log(`Applied slide-in animation to row ID: ${data.id}`);
              setTimeout(() => {
                $(row).removeClass("transition-new-row");
                console.log(
                  `Removed slide-in animation from row ID: ${data.id}`
                );
              }, 500); // Match animation duration
              setTimeout(() => {
                $(row).removeClass("highlight-new-row");
                console.log(`Removed highlight from row ID: ${data.id}`);
              }, 3000); // Remove highlight after 3 seconds
            }, 150); // Delay for DOM stability
          }
        },
        rowCallback: (row, data) => {
          $(row)
            .off("click")
            .on("click", (e) => {
              if (!$(e.target).hasClass("mark-paid")) {
                this.showTransactionalDataByID(data.id);
              }
            });
        },
      },
      totalRecords: 0,
      newTransactionIds: [],
    };
  },
  watch: {
    pageLength(newLength) {
      console.log(`Page length changed to: ${newLength}`);
      this.$refs.transactionTable.dt.page.len(newLength).draw();
    },
    filterOption() {
      console.log(`Filter option changed to: ${this.filterOption}`);
      this.$refs.transactionTable.dt.ajax.reload();
    },
    // searchValue() {
    //   console.log(`Search value changed to: ${this.searchValue}`);
    //   this.debounceSearch();
    // },

    // Existing watcher; add handling for clear
    searchValue(newVal, oldVal) {
      if (this.filterOption !== "date") {
        // existing non-date behavior (debounced)
        this.debounceSearch(); // debounce typing as before [web:54]
        return;
      }

      // If the date input is cleared, reset immediately
      if (!newVal || newVal.trim() === "") {
        this.clearDateAndReload(); // immediate reset on clear [web:55][web:71]
        return;
      }

      // For non-empty date input, follow your valid-only debounce flow
      if (this.isValidDDMMYYYY?.(newVal)) {
        this.debounceSearch(); // will call dt.ajax.reload() in your existing code [web:58]
      }
      // If invalid, do nothing (no reload) until user fixes it
    },

    activeTab(newTab) {
      console.log(`Active tab changed to: ${newTab}`);
      this.updateActionColumnVisibility();
      this.newTransactionIds = []; // Clear newTransactionIds on tab switch
      this.$refs.transactionTable.dt.ajax.reload();
    },
  },
  mounted() {
    console.log("Component mounted");
    this.fetchCountries();
    this.fetchShopId();
    this.bellAudio
      .play()
      .then(() => {
        this.bellAudio.pause();
        this.bellAudio.currentTime = 0;
        console.log("Bell audio initialized");
      })
      .catch((error) => {
        console.warn(
          "Audio initialization failed, user interaction required:",
          error
        );
        alert("Please interact with the page to enable notification sounds.");
      });

    this.$el.addEventListener("click", (event) => {
      if (event.target.classList.contains("mark-paid")) {
        const id = event.target.dataset.id;
        console.log(`Mark paid clicked for ID: ${id}`);
        this.confirmMarkPaid(id);
      }
    });

    this.$nextTick(() => {
      if (this.$refs.transactionTable && this.$refs.transactionTable.dt) {
        this.$refs.transactionTable.dt
          .column(5)
          .visible(this.activeTab === "unpaid" && this.isAdmin == 1);
        console.log("Initial Action column visibility set");
      }
    });
  },
  methods: {
    updateActionColumnVisibility() {
      if (this.$refs.transactionTable && this.$refs.transactionTable.dt) {
        this.$refs.transactionTable.dt
          .column(5)
          .visible(this.activeTab === "unpaid" && this.isAdmin == 1);
        console.log(
          `Action column visibility: ${
            this.activeTab === "unpaid" && this.isAdmin == 1
          }`
        );
      }
    },
    currency(value) {
      return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
      }).format(value);
    },
    async fetchShopId() {
      try {
        console.log("Fetching shop ID");
        const userResponse = await axios.get(
          `${import.meta.env.VITE_API_URL}user-detail`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
              "X-API-Secret": `${
                localStorage.getItem("x_api_key_secret") || ""
              }`,
            },
          }
        );
        const userData = userResponse.data.data;
        this.shopId =
          userData.isAdmin == 1
            ? userData.details.shop_id
            : userData.details.addedBy;
        this.isAdmin = userData.isAdmin;
        this.userId = userData.user_id;
        console.log(
          `Shop ID: ${this.shopId}, isAdmin: ${this.isAdmin}, userId: ${this.userId}`
        );

        this.updateActionColumnVisibility();
        this.setupWebSocket();
      } catch (userError) {
        console.error("Failed to fetch shop ID:", userError);
        alert("Failed to fetch shop ID");
      }
    },
    setupWebSocket() {
      if (!this.shopId || this.isAdmin === null) {
        console.log("Skipping WebSocket setup: shopId or isAdmin not set");
        return;
      }

      const channelPrefix =
        this.isAdmin == 1
          ? `admin.transactions.${this.shopId}`
          : `staff.transactions.${this.userId}`;
      console.log(`Setting up WebSocket for channel: ${channelPrefix}`);

      const attemptWebSocket = () => {
        console.log("SaleCreated Event attemptWebSocket");
        if (!window.Echo) {
          console.log("Echo not available, retrying in 1s");
          setTimeout(attemptWebSocket, 1000);
          return;
        }

        console.log("SaleCreated Event Before Listen");
        // console.log('Laravel Echo initialized successfully', window.Echo);

        window.Echo.channel(channelPrefix)
          .listen(".SaleCreated", (data) => {
            console.log("SaleCreated event received:", data);
            const transactionId =
              data?.billing?.id || data?.id || data?.transaction?.id;
            if (transactionId) {
              this.newTransactionIds.push(String(transactionId));
              console.log(
                `New transaction ID added: ${transactionId}`,
                this.newTransactionIds
              );
              this.playBellSound();
              this.$refs.transactionTable.dt.ajax.reload(() => {
                console.log("Table reloaded after SaleCreated");
              }, false);
              setTimeout(() => {
                this.newTransactionIds = this.newTransactionIds.filter(
                  (id) => id !== String(transactionId)
                );
                console.log(
                  `Removed transaction ID: ${transactionId}`,
                  this.newTransactionIds
                );
              }, 3000);
            } else {
              console.warn(
                "Transaction ID not found in SaleCreated event:",
                data
              );
            }
          })
          .listen(".TransactionUpdated", async (data) => {
            console.log("TransactionUpdated event received:", data);
            const transactionId =
              data?.billing?.id ||
              data?.id ||
              data?.transaction?.id ||
              data?.transaction_id;
            if (transactionId) {
              const rowIndex = this.$refs.transactionTable.dt
                .rows((idx, data) => data.id === transactionId)
                .indexes()[0];
              const rowNode = this.$refs.transactionTable.dt
                .rows((idx, data) => data.id === transactionId)
                .nodes()[0];
              console.log(
                `Row node for ID ${transactionId}:`,
                rowNode ? "Found" : "Not found"
              );

              if (rowNode && this.activeTab === "unpaid") {
                // Force repaint to ensure animation applies
                window.requestAnimationFrame(() => {
                  $(rowNode).addClass("transition-fade-out");
                  console.log(
                    `Applied fade-out animation to row ID: ${transactionId}`
                  );
                });
                // Wait for fade-out animation to complete
                await new Promise((resolve) => setTimeout(resolve, 600));
                console.log(
                  `Fade-out animation completed for row ID: ${transactionId}`
                );
                $(rowNode).css("display", "none"); // Hide row to prevent flicker
                $(rowNode).removeClass("transition-fade-out");
                // Manually remove row from DataTable
                this.$refs.transactionTable.dt.row(rowIndex).remove();
                console.log(
                  `Manually removed row ID: ${transactionId} from DataTable`
                );
              }

              this.newTransactionIds.push(String(transactionId));
              console.log(
                `Updated transaction ID added: ${transactionId}`,
                this.newTransactionIds
              );
              this.playBellSound();
              this.$refs.transactionTable.dt.ajax.reload(() => {
                console.log("Table reloaded after TransactionUpdated");
              }, false);
              setTimeout(() => {
                this.newTransactionIds = this.newTransactionIds.filter(
                  (id) => id !== String(transactionId)
                );
                console.log(
                  `Removed transaction ID: ${transactionId}`,
                  this.newTransactionIds
                );
              }, 3000);
            } else {
              console.warn(
                "Transaction ID not found in TransactionUpdated event:",
                data
              );
            }
          })
          .error((error) => console.error("WebSocket channel error:", error));
      };
      attemptWebSocket();
    },
    playBellSound() {
      this.bellAudio
        .play()
        .then(() => {
          console.log("Bell sound played");
          this.bellAudio.currentTime = 0;
        })
        .catch((error) => {
          console.error("Failed to play bell ringtone:", error);
        });
    },
    isNewTransaction(id) {
      const isNew = this.newTransactionIds.includes(String(id));
      console.log(`Checking if transaction ${id} is new: ${isNew}`);
      return isNew;
    },
    confirmMarkPaid(id) {
      if (confirm("Are you sure you want to mark this transaction as paid?")) {
        console.log(`Confirming mark paid for ID: ${id}`);
        this.markPaid(id);
      }
    },
    async markPaid(id) {
      try {
        console.log(`Marking transaction ${id} as paid`);
        await axios.get(
          `${import.meta.env.VITE_API_URL}${
            import.meta.env.VITE_GROCERY_GERMANY_PREFIX
          }transactions/${id}/mark-paid`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
              "X-API-Secret": `${
                localStorage.getItem("x_api_key_secret") || ""
              }`,
            },
          }
        );
        console.log(`Transaction ${id} marked as paid`);
        this.newTransactionIds.push(String(id));
        console.log(
          `Added transaction ID ${id} for animation`,
          this.newTransactionIds
        );
        // Table update handled by TransactionUpdated listener
      } catch (error) {
        console.error("Failed to mark as paid:", error);
        alert("Failed to mark as paid");
      }
    },
    debounceSearch: _.debounce(function () {
      console.log("Debounced search triggered");
      this.$refs.transactionTable.dt.ajax.reload();
    }, 300),
    async showTransactionalDataByID(id) {
      try {
        console.log(`Fetching transaction details for ID: ${id}`);
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}${
            import.meta.env.VITE_GROCERY_GERMANY_PREFIX
          }transaction/${id}`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
              "X-API-Secret": `${
                localStorage.getItem("x_api_key_secret") || ""
              }`,
            },
          }
        );

        if (response.data) {
          const data = response.data.data;
          const itemList = JSON.parse(data.item_list);
          let grandTotal = 0;

          this.$refs.itemTableBody.innerHTML = "";

          itemList.forEach((item, index) => {
            const amount = item.amount || item.rate * item.quantity;
            const status = item.isRefund === 1 ? "Refund" : "Sold";
            const amountHtml =
              item.isRefund === 1
                ? `− ${currency(-1 * amount)}`
                : currency(amount);

            const row = document.createElement("tr");

            const itemRate =
              item.isRefund == 1
                ? `− ${currency(item.rate)}`
                : currency(item.rate);

            // <td>₹${this.currency(item.rate)}${amountHtml}</td>

            row.innerHTML = `
              <td>${index + 1}</td>
              <td>${item.itemName}</td>
              <td>${item.quantity} ${item.selectedUnit}</td>
              <td style="width:15%">${itemRate}</td>
              <td><span class="badge ${
                status === "Refund"
                  ? "bg-danger text-white"
                  : "bg-info text-dark"
              }">${status}</span></td>
            `;
            this.$refs.itemTableBody.appendChild(row);

            grandTotal += parseFloat(amount);
          });

          const totalRow = document.createElement("tr");
          totalRow.id = "e_grand_total_row";
          totalRow.innerHTML = `
            <td></td>
            <td>Grand Total</td>
            <td></td>
            <td>${
              grandTotal < 0
                ? `− ${currency(-1 * grandTotal)}`
                : currency(grandTotal)
            }</td>
            <td></td>
            <td></td>
          `;
          this.$refs.itemTableBody.appendChild(totalRow);

          this.$refs.billingId.value = data.id;
          this.$refs.invoiceNumber.value = data.invoice_number;

          const modal = new bootstrap.Modal(
            this.$refs.transactionalDetailsModal
          );
          modal.show();
        }
      } catch (error) {
        console.error("Error fetching transaction:", error);
      }
    },
    generateInvoice(e) {
      e.preventDefault();
      const billId = this.$refs.billingId.value;
      console.log(`Generating invoice for bill ID: ${billId}`);
      const url = `/print/invoice/${billId}/`;
      window.open(url, "_blank");
    },

    // async sendToWhatsApp(e) {
    //   e.preventDefault();
    //   const billId = this.$refs.billingId.value;
    //   console.log(`Generating WhatsApp share link for bill ID: ${billId}`);
    //   try {
    //     const response = await axios.get(`/api/encrypt-bill-id/${billId}`, {
    //       headers: {
    //         Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
    //         "auth-key": `${localStorage.getItem("api_key") || ""}`,
    //         "X-API-Secret": `${localStorage.getItem("x_api_key_secret") || ""}`,
    //       },
    //     });
    //     const encryptedBillId = response.data.encrypted_id;
    //     // const invoiceUrl = `${window.location.origin}/invoice/${encryptedBillId}`;

    //     const invoiceUrl = encryptedBillId;

    //     const message = encodeURIComponent(`View your invoice: ${invoiceUrl}`);

    //     console.log(`Invoice: ${invoiceUrl}`);

    //     const whatsappUrl = `https://wa.me/?text=${message}`;
    //     window.open(whatsappUrl, "_blank");
    //     // console.log(`WhatsApp share link opened: ${whatsappUrl}`);
    //   } catch (error) {
    //     console.error("Error generating encrypted bill ID:", error);
    //     alert("Failed to generate WhatsApp share link");
    //   }
    // },

    // async sendToWhatsApp(e) {
    //   e.preventDefault();

    //   const billId = this.$refs.billingId.value;

    //   console.log(`Generating encrypted invoice URL for bill ID: ${billId}`);

    //   try {

    //     // // 1) Get encrypted ID
    //     // const encRes = await axios.get(`/api/encrypt-bill-id/${billId}`, {
    //     //   headers: {
    //     //     Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
    //     //     "auth-key": `${localStorage.getItem("api_key") || ""}`,
    //     //     "X-API-Secret": `${localStorage.getItem("x_api_key_secret") || ""}`,
    //     //   },
    //     // });

    //     // const encryptedBillId = encRes?.data?.encrypted_id;
    //     // if (!encryptedBillId) {
    //     //   throw new Error("Encrypted ID missing from response");
    //     // }

    //     // // If encryptedBillId is already a full short URL returned by backend, use as-is.
    //     // // Otherwise, build a URL using your domain pattern:
    //     // // const invoiceUrl = `${window.location.origin}/invoice/${encryptedBillId}`;
    //     // const invoiceUrl = encryptedBillId;

    //     // console.log(`Final invoice URL: ${invoiceUrl}`);

    //     // 1) Call the SMS API via POST with { invoiceURL }
    //     const smsPayload = {
    //       bill_id: billId
    //     };

    //     const smsRes = await axios.post(`${import.meta.env.VITE_API_URL}send-invoice-sms`, smsPayload, {
    //       headers: {
    //         "Content-Type": "application/json",
    //         Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
    //         "auth-key": `${localStorage.getItem("api_key") || ""}`,
    //         "X-API-Secret": `${localStorage.getItem("x_api_key_secret") || ""}`,
    //       },
    //     });

    //     // Optional: handle success response shape from your API
    //     console.log("SMS API response:", smsRes?.data);
    //     alert("Invoice link sent via SMS successfully");
    //   } catch (error) {
    //     console.error("Error sending invoice SMS:", error);
    //     alert("Failed to send invoice link via SMS");
    //   }
    // },

    async sendToWhatsApp(e) {
      e.preventDefault();

      const billId = this.$refs.billingId.value;
      console.log(`Opening SMS modal for bill ID: ${billId}`);

      // Set the current invoice number for display in modal
      this.currentInvoiceNumber = this.$refs.invoiceNumber.value;

      // Hide transactional details modal
      const modal1 = bootstrap.Modal.getInstance(
        this.$refs.transactionalDetailsModal
      );
      if (modal1) modal1.hide();

      // Show the share invoice modal
      const modal = new bootstrap.Modal(this.$refs.shareInvoiceModal);
      modal.show();
    },

    async fetchCountries() {
      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}countries-json`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
              "X-API-Secret": `${
                localStorage.getItem("x_api_key_secret") || ""
              }`,
            },
          }
        );

        if (response && response.data) {
          this.countries = response.data;
          console.log("Countries loaded:", this.userCountryCode);

          if (
            this.userCountryCode &&
            !this.countries.some((country) => country.code === this.countryCode)
          ) {
            this.countryCode = null; // Reset if user's code isn't in list
          }
        }
      } catch (error) {
        console.error("Error fetching countries:", error);
        // Fallback to default countries if API fails
        this.countries = [
          { code: "+91", name: "India" },
          { code: "+49", name: "Germany" },
        ];
      }
    },

    isValidPhoneNumber() {
      const phoneRegex = /^[0-9]{10,15}$/;
      return phoneRegex.test(this.phoneNumber.replace(/\D/g, ""));
    },

    async sendSMS() {
      if (!this.phoneNumber || !this.isValidPhoneNumber()) {
        alert("Please enter a valid mobile number");
        return;
      }

      // const fullPhoneNumber = this.countryCode + this.phoneNumber;
      const billId = this.$refs.billingId.value;

      console.log(`Sending SMS to: ${this.phoneNumber}, Bill ID: ${billId}`);

      try {
        const smsPayload = {
          bill_id: billId,
          country_code: this.countryCode,
          mobile: this.phoneNumber,
        };

        const smsRes = await axios.post(
          `${import.meta.env.VITE_API_URL}send-invoice-sms`,
          smsPayload,
          {
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${localStorage.getItem("token") || ""}`,
              "auth-key": `${localStorage.getItem("api_key") || ""}`,
              "X-API-Secret": `${
                localStorage.getItem("x_api_key_secret") || ""
              }`,
            },
          }
        );

        console.log("SMS API response:", smsRes?.data);

        // Close modal and show success
        // this.$refs.shareInvoiceModal._bsModal.hide();
        const modal = bootstrap.Modal.getInstance(this.$refs.shareInvoiceModal);
        if (modal) modal.hide();

        alert("Invoice link sent via SMS successfully!");

        // Reset form
        this.phoneNumber = "";
      } catch (error) {
        console.error("Error sending invoice SMS:", error);
        // alert("Failed to send invoice link via SMS. Please try again.");

        // Handle API validation errors (assuming Laravel returns { errors: { mobile: ['message'] } })
        if (error.response?.data?.data?.mobile) {
          // Extract first error message (or concatenate all)
          const mobileErrors = error.response.data.data.mobile || [];
          this.errorMessage = mobileErrors.join(". "); // e.g., "The mobile field is required. The mobile number is invalid."
        } else if (error.response?.data?.data?.country_code) {
          // Extract first error message (or concatenate all)
          const country_codeErrors =
            error.response.data.data.country_code || [];
          this.errorMessage = country_codeErrors.join(". "); // e.g., "The mobile field is required. The mobile number is invalid."
        } else if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage =
            "Failed to send invoice link via SMS. Please try again.";
        }
      }
    },

    // Mask the input to DD/MM/YYYY while typing
    onDateMaskedInput(e) {
      const prev = e.target.value || "";
      const digits = prev.replace(/\D/g, "").slice(0, 8); // ddmmyyyy
      let out = "";
      if (digits.length <= 2) {
        out = digits;
      } else if (digits.length <= 4) {
        out = digits.slice(0, 2) + "/" + digits.slice(2);
      } else {
        out =
          digits.slice(0, 2) + "/" + digits.slice(2, 4) + "/" + digits.slice(4);
      }
      // Assign to component state
      this.searchValue = out;
    },

    // Prevent non-digit user text before it reaches the input (keeps input clean)
    blockNonDigits(e) {
      if (e.inputType === "insertText" && /\D/.test(e.data || "")) {
        e.preventDefault();
      }
    },

    // On blur, optionally normalize leading zeros (e.g., 1/1/2025 -> 01/01/2025)
    onDateBlur(e) {
      if (!this.searchValue) return;
      if (!this.isValidDDMMYYYY(this.searchValue)) return;
      const [d, m, y] = this.searchValue.split("/");
      this.searchValue = [d.padStart(2, "0"), m.padStart(2, "0"), y].join("/");
    },

    // Strict DD/MM/YYYY check: regex + real calendar validation
    isValidDDMMYYYY(v) {
      if (!/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d\d$/.test(v)) {
        return false;
      }
      const [dd, mm, yyyy] = v.split("/").map(Number);
      const dt = new Date(yyyy, mm - 1, dd);
      // Ensure date object matches components (catches 31/02 etc.)
      return (
        dt.getFullYear() === yyyy &&
        dt.getMonth() === mm - 1 &&
        dt.getDate() === dd
      );
    },

    // Convert DD/MM/YYYY -> YYYY-MM-DD for API
    toIsoDate(v) {
      if (!this.isValidDDMMYYYY(v)) return null;
      const [dd, mm, yyyy] = v.split("/");
      return `${yyyy}-${mm}-${dd}`;
    },
    // Keep your existing debounceSearch; it triggers dt.ajax.reload()

    // Clear search and reload immediately
    clearDateAndReload() {
      const dt = this.$refs.transactionTable?.dt;
      if (!dt) return;
      // Clear DataTables search state and redraw
      dt.search("").draw(); // clears the global search box state [web:55]
      // For server-side processing, also reload via Ajax
      dt.ajax.reload(null, false); // keep current page if desired [web:71]
    },

    
  },

  beforeUnmount() {
    if (this.shopId && window.Echo && this.isAdmin !== null) {
      const channelPrefix =
        this.isAdmin == 1 ? "admin.transactions" : "staff.transactions";
      window.Echo.leave(`${channelPrefix}.${this.shopId}`);
      console.log(`Left WebSocket channel: ${channelPrefix}.${this.shopId}`);
    }
  },
};
</script>

<style>
/* Non-scoped to ensure styles apply to DataTable rows */
.table.dataTable .dataTables_length {
  display: none !important;
}

.table.dataTable {
  width: 100% !important;
}

.nav-tabs .nav-link {
  font-weight: 500;
  color: #495057;
}

.nav-tabs .nav-link.active {
  background-color: #f8f9fa;
  border-color: #dee2e6 #dee2e6 #fff;
}

.month-header {
  background-color: #e9ecef;
}

.highlight-new-row {
  background-color: #e6fffa !important;
  transition: background-color 1s ease-in-out;
}

.transition-new-row {
  animation: slideIn 0.5s ease-in-out forwards;
  -webkit-animation: slideIn 0.5s ease-in-out forwards;
  -moz-animation: slideIn 0.5s ease-in-out forwards;
}

.transition-fade-out {
  animation: fadeOut 0.5s ease-in-out forwards;
  -webkit-animation: fadeOut 0.5s ease-in-out forwards;
  -moz-animation: fadeOut 0.5s ease-in-out forwards;
}

@keyframes slideIn {
  0% {
    opacity: 0;
    transform: translateX(-20px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

.custom-input-group {
  display: flex;
  align-items: stretch;
}

/* .custom-select-wrapper {
  flex: 0 0 auto;
  width: auto;
}

.country-code-select1 {
  min-width: 120px !important;
  border-right: none !important;
  border-top-right-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
} */

.custom-select-wrapper {
  flex: 0 0 120px;
  background: #f8f9fa;
  border-right: 1px solid #ced4da;
}

.country-code-select1 {
  width: 100%;
  height: 50px;
  border: none;
  padding: 10px;
  background: transparent;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #495057;
  outline: none;
  transition: background 0.2s ease-in-out;
}

#phone_number {
  border-left: none;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
</style>