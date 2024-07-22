import React from 'react';
import { Backdrop, CircularProgress } from '@mui/material';

interface Props {
    isLoading: boolean
}

const AppLoading: React.FC<Props> = (props) => {

    let { isLoading } = props;

    return (
        <>
           <Backdrop
                sx={{ color: '#fff', zIndex: (theme) => theme.zIndex.drawer + 999999999 }}
                open={isLoading}
            >
                <CircularProgress color="inherit" />
            </Backdrop>
        </>
    );
}

export default AppLoading;