export const formatEventDate = value => value ? value.slice(0, 10).split('-').reverse().join('/') : '';
