import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/config/api';


export const useQuickSellStore = defineStore('quickSell', () => {
    // Initialize i18n
    const { t } = useI18n();

    // State
    const items = ref([]);
    const suggestions = ref([]);
    const units = ref([]);
    const loading = ref(false);
    const searchLoading = ref(false);
    const selectedItem = ref(null);
    const location = ref('sell'); // 'sell' or 'refund'


    // Computed
    const totalAmount = computed(() => {
        return items.value.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0);
    });


    const itemCount = computed(() => items.value.length);


    // Actions
    const fetchSuggestions = async (itemName) => {
        if (!itemName || itemName.trim().length < 1) {
            suggestions.value = [];
            return;
        }


        try {
            searchLoading.value = true;


            const formData = new FormData();
            formData.append('item_name', itemName);


            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}suggesstion-list`,
                formData
            );


            if (data.status === 'success' && data.data) {
                suggestions.value = data.data.map(item => ({
                    id: item.id,
                    name: item.item_name,
                    unit: item.short_unit,
                    stock: item.quantity,
                    rate: 0
                }));
            } else {
                suggestions.value = [];
            }
        } catch (error) {
            // console.error('Error fetching suggestions:', error);
            suggestions.value = [];
        } finally {
            searchLoading.value = false;
        }
    };


    const fetchRelatedUnits = async (itemId) => {
        try {
            loading.value = true;


            const formData = new FormData();
            formData.append('item_id', itemId);


            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}related-units`,
                formData
            );


            if (data.status === 'success') {
                units.value = data.units || [];
                return {
                    units: data.units,
                    defaultUnit: data.item_unit
                };
            }
        } catch (error) {
            console.error('Error fetching units:', error);
            units.value = [];
            return { units: [], defaultUnit: '' };
        } finally {
            loading.value = false;
        }
    };


    const checkStockQuantity = async (itemId, quantity, unit, isRefund=0) => {
        try {
            loading.value = true;


            const formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('quantity', quantity);
            formData.append('relatedUnit', unit);
            // formData.append('isRefund', location.value === 'refund' ? '1' : '0');
            formData.append('isRefund', isRefund);


            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}stock-quantity`,
                formData
            );


            if (data.status === 'success') {
                return {
                    success: true,
                    stockStatus: data.stockStatus,
                    itemData: data.data,
                    amount: data.amount,
                    available_stock: data.availableStock,
                    qauantity_added_on_cart: data.qauantity_added_on_cart,
                };
            }
            return { success: false, message: t('common.Stock check failed') };
        } catch (error) {
            console.error('Error checking stock:', error);
            return { success: false, message: error.response?.data?.message || t('common.Stock check failed') };
        } finally {
            loading.value = false;
        }
    };


    const addItemToCart = async (item) => {
        try {
            loading.value = true;


            const formData = new FormData();
            formData.append('item_id', item.id);
            formData.append('item_name', item.name);
            formData.append('sale_price', item.rate);
            formData.append('quantity', item.quantity);
            formData.append('item_unit', item.unit);
            formData.append('location', item.location);
            formData.append('isRefund', item.isRefund || 0);


            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}add-item-on-cart`,
                formData
            );


            if (data.status === 'success') {
                await fetchCartItems();
                return { success: true, message: t('common.Item added to cart successfully') };
            }
            return { success: false, message: data.message || t('common.Failed to add item') };
        } catch (error) {
            console.error('Error adding item to cart:', error);
            return {
                success: false,
                message: error.response?.data?.message || t('common.Failed to add item to cart')
            };
        } finally {
            loading.value = false;
        }
    };


    const updateCartItem = async (cartId, item) => {
        try {
            loading.value = true;

            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('item_id', item.id);
            formData.append('item_name', item.name);
            formData.append('sale_price', item.rate);
            formData.append('quantity', item.quantity);
            formData.append('location', item.location);
            formData.append('isRefund', item.isRefund || 0);


            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}update-item-on-cart`,
                formData
            );


            if (data.status === 'success') {
                await fetchCartItems();
                return { success: true, message: t('common.Item updated successfully') };
            }
            return { success: false, message: data.message || t('common.Failed to update item') };
        } catch (error) {
            console.error('Error updating cart item:', error);
            return {
                success: false,
                message: error.response?.data?.message || t('common.Failed to update item')
            };
        } finally {
            loading.value = false;
        }
    };


    const fetchCartItems = async () => {


        // console.log('fetchCartItems',location.value);


        try {
            loading.value = true;


            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}items-on-cart/${location.value}`
            );


            if (data.status === 'success') {
                items.value = data.data.map(item => {
                    const rate = parseFloat(item.sale_price);
                    const quantity = parseFloat(item.quantity);
                    const baseAmount = rate * quantity;


                    // If isRefund is 1, make amount negative
                    const amount = item.isRefund === 1
                        ? -Math.abs(baseAmount)
                        : baseAmount;


                    return {
                        cartId: item.id,
                        id: item.item_id,
                        name: item.item_name,
                        rate: rate,
                        quantity: quantity,
                        unit: item.item_unit,
                        amount: amount,
                        location: item.location,
                        isRefund: item.isRefund
                    };
                });
            }


            // console.log('fetchCartItems', items.value);


        } catch (error) {
            console.error('Error fetching cart items:', error);
            items.value = [];
        } finally {
            loading.value = false;
        }
    };



    const deleteCartItem = async (cartId) => {
        try {
            loading.value = true;


            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}delete-item-from-cart/${cartId}`
            );


            if (data.status === 'success') {
                await fetchCartItems();
                return { success: true, message: t('common.Item deleted successfully') };
            }
            return { success: false, message: data.message || t('common.Failed to delete item') };
        } catch (error) {
            console.error('Error deleting cart item:', error);
            return {
                success: false,
                message: error.response?.data?.message || t('common.Failed to delete item')
            };
        } finally {
            loading.value = false;
        }
    };


    const deleteAllCartItems = async () => {
        try {
            loading.value = true;


            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}delete-all-items-from-cart/${location.value}`
            );


            if (data.status === 'success') {
                items.value = [];
                return { success: true, message: t('common.All items deleted successfully') };
            }
            return { success: false, message: data.message || t('common.Failed to delete all items') };
        } catch (error) {
            console.error('Error deleting all cart items:', error);
            return {
                success: false,
                message: error.response?.data?.message || t('common.Failed to delete all items')
            };
        } finally {
            loading.value = false;
        }
    };


    // const submitBilling = async (options = {}) => {
    //     try {
    //         loading.value = true;

    //         const formData = new FormData();

    //         items.value.forEach((item, index) => {
    //             formData.append(`itemList[${index}][itemId]`, item.id);
    //             formData.append(`itemList[${index}][itemName]`, item.name);
    //             formData.append(`itemList[${index}][quantity]`, item.quantity.toFixed(2));
    //             formData.append(`itemList[${index}][rate]`, item.rate.toFixed(2));
    //             formData.append(`itemList[${index}][selectedUnit]`, item.unit.toUpperCase());
    //             formData.append(`itemList[${index}][amount]`, Math.abs(Math.round(item.amount)));
    //             formData.append(`itemList[${index}][isDelete]`, 0);
    //             formData.append(`itemList[${index}][isRefund]`, item.isRefund || 0);
    //         });

    //         formData.append('grand_total', Math.abs(Math.round(totalAmount.value)));
    //         formData.append('print', options.print ? 'true' : 'false');
    //         formData.append('isSendSms', options.isSendSms ? 'true' : 'false');

    //         // if (options.isSendSms && options.mobile) {
    //         //     formData.append('mobile', options.mobile);
    //         //     formData.append('country_code', options.country_code || 'IN');
    //         // }

    //         // ← CHANGED: append customer fields when provided (GST-compliant flow)
    //         if (options.customer_mobile) {
    //             formData.append('customer_mobile', options.customer_mobile.trim());
    //             formData.append('country_code', options.country_code || 'IN');
    //         }
    //         if (options.customer_name) {
    //             formData.append('customer_name', options.customer_name.trim());
    //         }
    //         if (options.customer_address) {
    //             formData.append('customer_address', options.customer_address.trim());
    //         }
    //         if (options.customer_gstin) {
    //             formData.append('customer_gstin', options.customer_gstin.trim());
    //         }

    //         const { data } = await api.post(
    //             `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}billing-n-refund`,
    //             formData
    //         );

    //         if (data.status === 'success') {
    //             return {
    //                 success: true,
    //                 message: t('common.Billing completed successfully'),
    //                 data: {
    //                     bill_id: data.bill_id || data.invoice_id || data.id || data.data?.bill_id,
    //                     ...data
    //                 }
    //             };
    //         }
    //         return { success: false, message: data.message || t('common.Failed to process billing') };
    //     } catch (error) {
    //         console.error('Error submitting billing:', error);
    //         return {
    //             success: false,
    //             message: error.response?.data?.message || t('common.Failed to process billing')
    //         };
    //     } finally {
    //         loading.value = false;
    //     }
    // };


    const submitBilling = async (options = {}) => {
        try {
            loading.value = true;

            const formData = new FormData();

            items.value.forEach((item, index) => {
                formData.append(`itemList[${index}][itemId]`, item.id);
                formData.append(`itemList[${index}][itemName]`, item.name);
                formData.append(`itemList[${index}][quantity]`, item.quantity.toFixed(2));
                formData.append(`itemList[${index}][rate]`, item.rate.toFixed(2));
                formData.append(`itemList[${index}][selectedUnit]`, item.unit.toUpperCase());
                formData.append(`itemList[${index}][amount]`, Math.abs(Math.round(item.amount)));
                formData.append(`itemList[${index}][isDelete]`, 0);
                formData.append(`itemList[${index}][isRefund]`, item.isRefund || 0);
            });

            formData.append('grand_total', Math.abs(Math.round(totalAmount.value)));
            formData.append('print', options.print ? 'true' : 'false');
            formData.append('isSendSms', options.isSendSms ? 'true' : 'false');

            if (options.customer_mobile) {
                formData.append('customer_mobile', options.customer_mobile.trim());
                formData.append('country_code', options.country_code || 'IN');
            }
            if (options.customer_name) formData.append('customer_name', options.customer_name.trim());
            if (options.customer_address) formData.append('customer_address', options.customer_address.trim());
            if (options.customer_gstin) formData.append('customer_gstin', options.customer_gstin.trim());

            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}billing-n-refund`,
                formData
            );

            if (data.status === 'success') {
                return {
                    success: true,
                    message: t('common.Billing completed successfully'),
                    data: {
                        bill_id: data.bill_id || data.invoice_id || data.id || data.data?.bill_id,
                        ...data
                    }
                };
            }

            // ← Non-success (e.g. status: "failed" with validation errors)
            return {
                success: false,
                message: data.message || t('common.Failed to process billing'),
                errors: data.data || {},   // ← { customer_mobile: ["...", "..."] }
            };

        } catch (error) {
            const errBody = error.response?.data;
            return {
                success: false,
                message: errBody?.message || t('common.Failed to process billing'),
                errors: errBody?.data || {},  // ← same shape for HTTP 422
            };
        } finally {
            loading.value = false;
        }
    };

    const clearItems = () => {
        items.value = [];
    };


    const clearSuggestions = () => {
        suggestions.value = [];
    };


    const clearUnits = () => {
        units.value = [];
    };


    const setSelectedItem = (item) => {
        selectedItem.value = item;
    };


    const setLocation = (newLocation) => {
        location.value = newLocation;
    };

    // ── Search Customer by Mobile ───────────────────────────────────────────────
    const searchCustomer = async (mobile) => {
        try {
            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}search-customer/${mobile}`
            );

            if (data.status === 'success' && data.data) {
                return {
                    success: true,
                    customer: data.data
                };
            }
            return { success: false, customer: null };
        } catch (error) {
            console.error('Error searching customer:', error);
            return { success: false, customer: null };
        }
    };

    return {
        // State
        items,
        suggestions,
        units,
        loading,
        searchLoading,
        selectedItem,
        location,


        // Computed
        totalAmount,
        itemCount,


        // Actions
        fetchSuggestions,
        fetchRelatedUnits,
        checkStockQuantity,
        addItemToCart,
        fetchCartItems,
        updateCartItem,
        deleteCartItem,
        deleteAllCartItems,
        submitBilling,
        searchCustomer,
        clearItems,
        clearSuggestions,
        clearUnits,
        setSelectedItem,
        setLocation
    };
});
