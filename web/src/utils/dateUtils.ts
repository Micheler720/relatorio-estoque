export function getDateFormat(date: Date) : string {
    let month = date.getMonth() + 1;
    let monthString = month.toString();
    
    if(month < 10) 
        monthString = `0${month}`;

    return `${date.getFullYear()}-${monthString}-${date.getDate()}`
}
