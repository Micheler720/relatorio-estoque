import * as Yup from 'yup';


interface ErrorMessages {
    messages: string[];
}

interface ErrorModel {
    errors: ErrorMessages;
}

export interface FormErrors {
    [Key: string]: string;
}

interface ErrorMessages {
    messages: string[];
}

export default class FormService {
    private setErrors: any;
    private setFormErrors: any;
    private data: any = {};
    private setData: any = {};
    private formErros: FormErrors = {};
  
    constructor(data:any, setData: any, setErrors:any, setFormErrors?:any, formErros?: any) {
      this.data = data;
      this.setData = setData;
      this.setErrors = setErrors;
      this.setFormErrors = setFormErrors;
    }

    public setInputValue(inputName:string, value: any) {
        this.setData({...this.data, [inputName]: value});
        this.cleanErrorInput(inputName);
        
    }
    
    public handleErros(err: any) {  
        if (err instanceof Yup.ValidationError) {
            var erros = this.getValidationErrors(err);  
            console.log(erros);
            this.setFormErrors(erros);
            return;
        }    
            
        if(err.response.status === 400) {
            const errors = err.response.data.messages;
            this.setFormErrors(errors);
            return;
        }

        this.setErrors(this.getErrors(err));
    }

    public getErrors(err: any): string[] {
          
        if (err.response.data.status === 500) {
            return err.response?.data.messages;
        }
    
        if (err.status >= 400 && err.status < 500) {
            return err.errors.mensagens;
        } 
    
        return ["Houve uma falha inesperada. Contate o suporte técnico"];
    }
    
    public getValidationErrors(err: Yup.ValidationError): FormErrors {
    
        const { inner } = err;
        let errors: FormErrors = {};
        inner.forEach(error => {
            if(error.path !== null && error.path !== undefined)
                errors[error.path!] = error.message;
        });
    
        return errors;
    }

    public cleanErrors() {
        this.setErrors([]);
        this.setFormErrors({} as FormErrors);
    }   
    
    private cleanErrorInput(inputName: string) {        
        this.setFormErrors({...this.formErros, [inputName]: ""});
    }  
}
