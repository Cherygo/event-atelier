export const amountInput = minor => minor === null || minor === undefined ? '' : (minor / 100).toFixed(2);

export const formatBudgetAmount = (minor, currency) => {
    if (minor === null || minor === undefined || !currency) return 'Not set';
    return new Intl.NumberFormat(undefined, { style: 'currency', currency, currencyDisplay: 'code' }).format(minor / 100);
};
