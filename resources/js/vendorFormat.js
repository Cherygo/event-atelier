export function formatQuote(vendor) {
    if (vendor.quote_amount === null || vendor.quote_amount === undefined || vendor.quote_amount === '') return 'Not quoted';
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: vendor.currency, currencyDisplay: 'code' }).format(Number(vendor.quote_amount));
}
