// @/composables/useInputValidation.js
import { ref, getCurrentInstance } from "vue";

// ─── Unit config ───────────────────────────────────────────────────────────
const DECIMAL_UNITS = ["g", "kg", "ml", "l"];

// ─── Formatting config per country ────────────────────────────────────────
// Used ONLY for display formatting, NOT for input or calculation
const COUNTRY_FORMAT_CONFIG = {
    // dot countries  → thousands = comma,  decimal = dot
    IN: { thousands: ",", decimal: ".", indianStyle: true },
    US: { thousands: ",", decimal: ".", indianStyle: false },
    GB: { thousands: ",", decimal: ".", indianStyle: false },
    // comma countries → thousands = dot,   decimal = comma
    DE: { thousands: ".", decimal: ",", indianStyle: false },
    FR: { thousands: ".", decimal: ",", indianStyle: false },
    IT: { thousands: ".", decimal: ",", indianStyle: false },
    ES: { thousands: ".", decimal: ",", indianStyle: false },
};

const DEFAULT_COUNTRY = "IN";

// ─── Always-allowed keyboard keys ─────────────────────────────────────────
const ALWAYS_ALLOWED_KEYS = new Set([
    "Backspace", "Delete", "Tab",
    "ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown",
    "Home", "End",
]);

const isDigitKey = (key) => /^[0-9]$/.test(key);

// ─── Pure Helpers ──────────────────────────────────────────────────────────

const getQuantityConfig = (unit = "") => {
    const isDecimal = DECIMAL_UNITS.includes(unit?.toLowerCase()?.trim());
    return {
        isDecimal,
        min: isDecimal ? 0.1 : 1,
        step: isDecimal ? 0.1 : 1,
    };
};

/**
 * Format number for Indian style: 1,35,000.45
 * Last 3 digits grouped, then groups of 2
 */
const formatIndian = (intPart, thousandsSep) => {
    if (intPart.length <= 3) return intPart;
    const last3 = intPart.slice(-3);
    const rest = intPart.slice(0, -3);
    const groups = [];
    let i = rest.length;
    while (i > 0) {
        groups.unshift(rest.slice(Math.max(0, i - 2), i));
        i -= 2;
    }
    return groups.join(thousandsSep) + thousandsSep + last3;
};

/**
 * Format number for standard style: 1,350,000.45
 * Groups of 3
 */
const formatStandard = (intPart, thousandsSep) => {
    return intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
};

/**
 * Format a raw JS float number for DISPLAY only
 * Input:  1350000.45  (always a JS number)
 * Output: "1,35,000.45" (IN) or "1.350.000,45" (DE)
 *
 * @param {number} value       - raw JS float
 * @param {string} countryCode - e.g. "IN", "DE"
 * @param {number} decimals    - decimal places (default 2)
 */
const formatForDisplay = (value, countryCode = DEFAULT_COUNTRY, decimals = 2) => {
    if (value === null || value === undefined || isNaN(value)) return "";

    const config =
        COUNTRY_FORMAT_CONFIG[countryCode?.toUpperCase()] ??
        COUNTRY_FORMAT_CONFIG[DEFAULT_COUNTRY];

    // Always use dot for internal toFixed calculation
    const fixed = Math.abs(value).toFixed(decimals);
    const [intPart, decPart] = fixed.split(".");

    const formattedInt = config.indianStyle
        ? formatIndian(intPart, config.thousands)
        : formatStandard(intPart, config.thousands);

    const sign = value < 0 ? "−" : "";

    return decimals > 0
        ? `${sign}${formattedInt}${config.decimal}${decPart}`
        : `${sign}${formattedInt}`;
};

/**
 * Sanitize raw input string (dot always used as input decimal)
 * For decimal units → allow digits + dot only
 * For integer units → digits only
 */
const applySanitize = (value, allowDecimal) => {
    if (!allowDecimal) {
        return value.replace(/[^0-9]/g, "");
    }
    // Always dot for input regardless of country
    let clean = value.replace(/[^0-9.]/g, "");
    // Only one dot allowed
    const parts = clean.split(".");
    if (parts.length > 2) {
        clean = parts[0] + "." + parts.slice(1).join("");
    }
    return clean;
};

// ─── Composable ────────────────────────────────────────────────────────────

export function useInputValidation() {
    const validationErrors = ref([]);

    // ── Resolve from global properties ──────────────────────────────────────
    const instance = getCurrentInstance();
    const globalProps = instance?.appContext?.config?.globalProperties;

    const countryCode =
        globalProps?.$countryCode?.toUpperCase() ?? DEFAULT_COUNTRY;

    // $decimalSeparator is used ONLY for display formatting
    // Input always uses dot (.) — calculation always uses JS float
    const displayDecimalSeparator =
        globalProps?.$decimalSeparator ?? ".";

    // ─── Key Handlers ─────────────────────────────────────────────────────────

    /**
     * QUANTITY keydown guard
     * Input always uses dot regardless of country
     */
    const restrictQuantityKeys = (event, item = null) => {
        const key = event.key;
        const unit = item?.unit ?? "";
        const { isDecimal } = getQuantityConfig(unit);

        if (ALWAYS_ALLOWED_KEYS.has(key)) return;
        if (event.ctrlKey || event.metaKey) return;
        if (isDigitKey(key)) return;

        // Allow dot for decimal units only (input always uses dot)
        if (isDecimal && key === ".") {
            if (event.target.value.includes(".")) {
                event.preventDefault(); // block second dot
            }
            return;
        }

        event.preventDefault(); // block everything else
    };

    /**
     * RATE keydown guard
     * Rate always allows decimals — input always uses dot
     */
    const restrictRateKeys = (event) => {
        const key = event.key;

        if (ALWAYS_ALLOWED_KEYS.has(key)) return;
        if (event.ctrlKey || event.metaKey) return;
        if (isDigitKey(key)) return;

        if (key === ".") {
            if (event.target.value.includes(".")) {
                event.preventDefault(); // block second dot
            }
            return;
        }

        event.preventDefault(); // block everything else
    };

    // ─── Input Sanitizers (paste/autofill defense) ────────────────────────────

    const sanitizeQuantityInput = (event, item = null) => {
        const unit = item?.unit ?? "";
        const { isDecimal } = getQuantityConfig(unit);
        const sanitized = applySanitize(event.target.value, isDecimal);
        if (event.target.value !== sanitized) {
            event.target.value = sanitized;
        }
    };

    const sanitizeRateInput = (event) => {
        const sanitized = applySanitize(event.target.value, true);
        if (event.target.value !== sanitized) {
            event.target.value = sanitized;
        }
    };

    // ─── Validators ───────────────────────────────────────────────────────────

    const validateField = (value, fieldName, options = {}) => {
        const { min = 0, required = true, allowDecimal = true } = options;
        // Always parse with dot — input always uses dot
        const num = parseFloat(value);

        if (required && (value === "" || value === null || value === undefined)) {
            return `${fieldName} is required.`;
        }
        if (isNaN(num)) {
            return `${fieldName} must be a valid number.`;
        }
        if (!allowDecimal && !Number.isInteger(num)) {
            return `${fieldName} must be a whole number.`;
        }
        if (num < min) {
            return `${fieldName} must be at least ${min}.`;
        }
        return null;
    };

    const validateQuantity = (value, unit = "", t = null) => {
        const { isDecimal, min } = getQuantityConfig(unit);
        const label = t ? t("common.Quantity") : "Quantity";
        return validateField(value, label, { min, allowDecimal: isDecimal });
    };

    const validateRate = (value, t = null) => {
        const label = t ? t("common.Rate") : "Rate";
        return validateField(value, label, { min: 0, allowDecimal: true });
    };

    const validateCartItem = (item, t) => {
        const errors = [];
        const qtyError = validateQuantity(item.quantity, item.unit, t);
        if (qtyError) errors.push(qtyError);
        const rateError = validateRate(item.rate, t);
        if (rateError) errors.push(rateError);
        return errors;
    };

    /**
     * Parse input value to JS float for calculation
     * Input always uses dot so plain parseFloat works
     */
    const parseValue = (value) => {
        if (value === null || value === undefined || value === "") return 0;
        return parseFloat(value) || 0;
    };

    /**
     * Format a JS float for DISPLAY using country rules
     * IN  → 1,35,000.45
     * DE  → 1.350.000,45
     * US  → 1,350,000.45
     */
    const formatDisplay = (value, decimals = 2) => {
        return formatForDisplay(value, countryCode, decimals);
    };

    const clearErrors = () => {
        validationErrors.value = [];
    };

    return {
        // State
        validationErrors,
        // Resolved info
        countryCode,
        displayDecimalSeparator,
        // Key handlers
        restrictQuantityKeys,
        restrictRateKeys,
        // Input sanitizers
        sanitizeQuantityInput,
        sanitizeRateInput,
        // Validators
        validateQuantity,
        validateRate,
        validateCartItem,
        // Helpers
        getQuantityConfig,
        parseValue,
        formatDisplay,
        clearErrors,
    };
}