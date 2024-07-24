import React from 'react';
import { Box } from '@mui/material';
import { DesktopDatePicker, DesktopDatePickerProps } from '@mui/x-date-pickers';
import AppInputError from './AppInputError';
import { Moment } from 'moment';

interface AppDatePickerProps extends DesktopDatePickerProps<Moment, false> {
  fullWidth?: boolean;
  errorMessage?: string;
}

const AppDatePicker: React.FC<AppDatePickerProps> = (props) => {
  return (
    <Box sx={{ }}>
      <DesktopDatePicker
        format='DD/MM/YYYY'
        slotProps={{
          textField: {
            label: props.label || "Data",
            size: 'small',
            sx: {
              padding: 0,
              borderRadius: 18,
              borderWidth: '0px',
              border: `0px solid ${props.errorMessage ? 'red!important' : 'grey'}`,
              width: props.fullWidth ? '100%' : 'auto',
            }
          }
        }}
        {...props}
      />
      <AppInputError error={props.errorMessage} />
    </Box>
  );
}


export default AppDatePicker;