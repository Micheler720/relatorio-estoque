import React from 'react';

import { List, ListItem, ListItemText } from '@mui/material';
import { ErrorOutlined } from '@mui/icons-material';

interface Erros{
    errors: string[]
}

const AppErrorList: React.FC<Erros> = (props) => {
    let errors = props.errors;
    return (
        <>
           {errors.length > 0 &&
            <List dense={true} sx={{ }}>
                {errors.map(erro => 
                    <ListItem key={erro} >
                        <ErrorOutlined fontSize="small" color='error' />                                            
                        <ListItemText sx={{ marginLeft: "4px", lineHeight: "32px", fontWeight: "bold", color: "red" }} primary={erro}/>
                    </ListItem>                                        
                )}
            </List>
            }
        </>
    );
}

export default AppErrorList;