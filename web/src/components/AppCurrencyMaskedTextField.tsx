import React from 'react';

import { Box, OutlinedInputProps, Typography } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import MaskedInput from 'react-text-mask'
import AppInputError from './AppInputError';
import createNumberMask from 'text-mask-addons/dist/createNumberMask'



interface FormTextFieldProps extends OutlinedInputProps {
  name: string,
  id?: string,
  label?: string,
  value?: string,
  type?: string,
  mask: string;
  onRawValueChange?: (value: string) => void;
  setFormErros?: any;
  formErros?: any;
  errorMessage?: string;
}

const defaultMaskOptions = {
  prefix: 'R$ ',
  suffix: '',
  includeThousandsSeparator: true,
  thousandsSeparatorSymbol: '.',
  allowDecimal: true,
  decimalSymbol: ',',
  decimalLimit: 2, // how many digits allowed after the decimal
  integerLimit: 10, // limit length of integer numbers
  allowNegative: false,
  allowLeadingZeroes: false,
}

const AppCurrencyMaskedTextField: React.FC<FormTextFieldProps> = (props) => {

  const currencyMask = createNumberMask({
    ...defaultMaskOptions
  })

  let {
    name,
    mask,
    sx,
    label,
    errorMessage,
    type,
    id,
    ...rest } = props;
  const theme = useTheme();
  
  let hasError = errorMessage !== undefined && errorMessage !== null && errorMessage !== "";


  return (
    <Box sx={{position: 'relative'}}>
      <Box sx={{
        pointerEvents: props.disabled ? "none" : "auto",
        border: `1px solid ${hasError ? "#E17373" : "#ccc"}`,
        position: "relative",
        padding: "10px",
        display: "inline-flex",
        borderRadius: "4px",
        width: props.fullWidth ? "100%" : "auto",
        color: "#00000099",
        "&:focus-within": {
          border: `1px solid ${hasError ? "#E17373" : theme.palette.primary.main}!important`,
          outline: `1px solid ${hasError ? "#E17373" : theme.palette.primary.main}`,
          color: `${theme.palette.primary.main} !important`,
        },
        "&:hover": {
          border: `1px solid ${hasError ? "#E17373" : "#000"}`,
        }
      }}>
          <Typography sx={{ 
            position: "absolute", 
            top: "-10px", 
            fontSize: "13px", 
            fontFamily: theme.typography.fontFamily, 
            backgroundColor: "#fff", 
            padding: "0 4px"
          }}>
            {label}
          </Typography> 
        <MaskedInput
          id={id}
          mask={currencyMask}
          autoComplete='off'
          value={props.value}
          onChange={(e) => {
            if (rest.onChange) {
              rest.onChange(e);
            }

            if (props.onRawValueChange) {
              props.onRawValueChange(e.target.value.replace(/[^0-9]/g, ''));
            }

          }}
          style={
            {
              paddingLeft: "4px",
              backgroundColor: 'transparent',
              border: 'none',
              outline: 'none',
              color: props.disabled ? "#999" : "#222",
              fontSize: "16px"
            }
          }
          
        />
      </Box>


     <AppInputError error={errorMessage} />
    </Box>
  );
}

export default AppCurrencyMaskedTextField;