import { ErrorOutlined } from '@mui/icons-material';
import { Box, Typography } from '@mui/material';
import React from 'react';

export interface AppInputErroProps {
  error?: string | undefined | null;
}

const AppInputError: React.FC<AppInputErroProps> = (props) => {
  const { error } = props;
  
  return (
    <>
      { 
      error && 
          <Box sx={{display: "flex", alignItems: "center", position: "absolute", mt: "4px"}}>
              <ErrorOutlined fontSize="small" color='error' sx={{ marginRight: "4px", padding: "2px 0" }} /> 
              <Typography sx={{ color: "red", fontSize: "10px !important", fontWeight: "bold" }}>{error}</Typography>
          </Box>
      }
    </>
  );
}

export default AppInputError;