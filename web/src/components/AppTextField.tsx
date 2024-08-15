import React from 'react';
import { Box, IconButton, InputAdornment, OutlinedInput, OutlinedInputProps } from '@mui/material';
import { Visibility, VisibilityOff } from '@mui/icons-material';
import AppInputError from './AppInputError';

interface FormTextFieldProps extends OutlinedInputProps {
    name: string,
    id?: string,
    label?: string,
    type?: string,
    inputValue?: any;
    setInputValue?: any;
    errorMessage?: string;
}

const AppTextField: React.FC<FormTextFieldProps> = (props) => {

    let { name, sx, label, errorMessage, type, ...rest } = props;
    const [showPassword, setShowPassword] = React.useState(false);

    const handleClickShowPassword = () => setShowPassword((show) => !show);
    const handleMouseDownPassword = (event: React.MouseEvent<HTMLButtonElement>) => {
        event.preventDefault();
    };

    const hasError = errorMessage !== undefined && errorMessage !== null && errorMessage !== "";

    return (
        <Box sx={{ position: 'relative' }}>
            <OutlinedInput
                size='small'
                notched
                label={label}
                onChange={(e) => { rest.onChange && rest.onChange(e); }}
                inputProps={{ shrink: true }}
                sx={{
                    "svg > path": {
                        fill: "#4d4d4d"
                    },
                    zIndex: 999999,
                    ...sx
                }}
                {...rest}
                error={hasError}
                type={type && type === "password" ? (showPassword ? "text" : "password") : type}
                endAdornment={
                    type && type === "password" &&
                    <InputAdornment position="end">
                        <IconButton
                            aria-label="toggle password visibility"
                            onClick={handleClickShowPassword}
                            onMouseDown={handleMouseDownPassword}
                        >
                            {showPassword ? <VisibilityOff /> : <Visibility />}
                        </IconButton>
                    </InputAdornment>
                }
            />
            <AppInputError error={errorMessage} />
        </Box>


    );
}

export default AppTextField;