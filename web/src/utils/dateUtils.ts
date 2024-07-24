export function getDateFormat(date: Date) : string {
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`
}

export function getMonthYearFormat(date: Date) : string {
    return `${date.getFullYear()}-${date.getMonth() + 1}`
}