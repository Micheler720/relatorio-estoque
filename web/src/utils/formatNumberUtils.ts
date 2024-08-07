import {TypeValues} from "../enums/typeValues";

export function getFormatNumber(value: string, typeValue: string) : string {
   switch (Number(typeValue) as TypeValues) {
    case TypeValues.Currency:
        return Number(value).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })
    case TypeValues.Percentual:
        return Number(value)?.toLocaleString("pt-BR") + " %"
    case TypeValues.Numero:
        return Number(value)?.toLocaleString("pt-BR")
    default:
        return value;
   }
}