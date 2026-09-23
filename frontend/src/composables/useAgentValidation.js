import { useI18n } from "vue-i18n";

export function useAgentValidation() {
    const { t } = useI18n();

    // ─── Checkers ─────────────────────────────────────────────────────────────────
    const isPhoneLike = (value) =>
        /^[+\d\s\-().]+$/.test(value.trim()) && !value.includes("@");

    const isValidEmail = (value) =>
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());

    // ─── Step 1 Validators ────────────────────────────────────────────────────────
    const validateEmail = (value) => {
        if (!value) return t("common.Email is required to continue") + ".";
        if (isPhoneLike(value))
            return t("common.valid_email_not_phone_number") + ".";
        if (!isValidEmail(value))
            return t("common.Please enter a valid email address") + ".";
        return "";
    };

    const validatePassword = (value) => {
        if (!value) return t("common.Password is required to continue") + ".";
        if (value.length < 8)
            return t("common.Password must be at least 8 characters") + ".";
        return "";
    };

    const validateConfirmPassword = (password, confirmPassword) => {
        if (!confirmPassword) return t("common.Please confirm your password") + ".";
        if (password !== confirmPassword)
            return t("common.Passwords do not match") + ".";
        return "";
    };

    // ─── Step 2 Validators ────────────────────────────────────────────────────────
    const validateFullName = (value) => {
        if (!value || !value.trim())
            return t("common.Full name is required") + ".";
        return "";
    };

    const validateAddress = (value) => {
        if (!value || !value.trim())
            return t("common.Address is required") + ".";
        return "";
    };

    const validateMobile = (value) => {
        if (!value || !value.trim())
            return t("common.Mobile number is required") + ".";
        if (!/^\+?[0-9]{7,15}$/.test(value.replace(/\s/g, "")))
            return t("common.Please enter a valid mobile number") + ".";
        return "";
    };

    const validatePAN = (value) => {
        if (!value || !value.trim())
            return t("common.PAN number is required") + ".";
        if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value))
            return t("common.valid_pan_validation") + ".";
        return "";
    };

    // ─── Generic Validators (reusable anywhere) ───────────────────────────────────
    const validateRequired = (value, fieldName = "This field") => {
        if (!value || !String(value).trim())
            return `${fieldName} is required.`;
        return "";
    };

    const validatePhone = (value) => {
        if (!value || !value.trim())
            return t("common.Phone number is required") + ".";
        if (!/^\+?[0-9]{7,15}$/.test(value.replace(/\s/g, "")))
            return t("common.Please enter a valid phone number") + ".";
        return "";
    };

    // ─── Bulk Runner ──────────────────────────────────────────────────────────────
    /**
     * Runs multiple validators and returns all error strings.
     * Empty array = all passed.
     *
     * Usage:
     * const errs = runValidations([
     *   { validator: validateEmail,    args: [form.email] },
     *   { validator: validatePassword, args: [form.password] },
     * ]);
     */
    const runValidations = (rules) =>
        rules.map(({ validator, args }) => validator(...args)).filter(Boolean);

    // ─── Exports ──────────────────────────────────────────────────────────────────
    return {
        // checkers
        isPhoneLike,
        isValidEmail,
        // step 1
        validateEmail,
        validatePassword,
        validateConfirmPassword,
        // step 2
        validateFullName,
        validateAddress,
        validateMobile,
        validatePAN,
        // generic
        validateRequired,
        validatePhone,
        // bulk runner
        runValidations,
    };
}