import React  from 'react'
import { Box, MenuItem, Select, SelectProps } from '@mui/material';
import AppInputError from './AppInputError';

export type AppSelectProps = SelectProps<any> &
{
  options: AppSelectOption[];
  errorMessage?: string;
}

export interface AppSelectOption {
  value: any;
  label: string;
}

const AppSelect: React.FC<AppSelectProps> = (props) => {
  const { errorMessage } = props;
  const hasError = errorMessage !== undefined && errorMessage !== null && errorMessage !== "";
  
  return (
    <Box sx={{position:"relative"}}>
      <Select
        {...props}
        labelId={`row-select-${props.name}`}
        id={`row-select-${props.name}`}
        error={hasError}
        inputProps={{ shrink: true }}
      >
        {props.options.map((option, index) => {
          return (
            <MenuItem key={index} value={option.value}>{option.label}</MenuItem>
          );
        })}
      </Select>
      <AppInputError error={errorMessage} />
    </Box>
  )
}

export default AppSelect;