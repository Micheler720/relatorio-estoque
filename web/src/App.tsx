import React, { FormEvent, useState } from 'react';
import Card from '@mui/material/Card';
import moment from 'moment';
import * as Yup from 'yup';
import { Button, Checkbox, FormControlLabel, Paper, styled, Table, TableBody, TableCell, tableCellClasses, TableContainer, TableHead, TableRow, Typography } from "@mui/material";
import { Assessment, BorderAll, FilterAlt } from '@mui/icons-material';
import { Box } from '@mui/material';
import ArrowBackIosIcon from '@mui/icons-material/ArrowBackIos';
import AppDatePicker from './components/AppDatePicker';
import FormService, { FormErrors } from './services/form.services';
import AppSelect from './components/AppSelect';
import AppTextField from './components/AppTextField';
import AppLoading from './components/AppLoading';
import api from './services/api';
import { getDateFormat } from './utils/dateUtils';
import AppErrorList from './components/AppErrorList';

const formSchema = Yup.object({
  dataInicial: Yup
    .date()
    .required('Obrigatório informar a data inicial.'),
  dataFinal: Yup
    .date()
    .min(Yup.ref('dataInicial'), 'Data final deve ser maior que a data inicial')
    .required('Obrigatório informar a data final.'),
  filial: Yup
    .number()
    .required('Obrigatório informar a filial.')
    .min(1, 'Obrigatório informar a filial.')
});

interface FiltrosRelatorio {
  dataInicial: string;
  dataFinal: string;
  filial: number;
  estoqueInicial?: number;
  estoqueFinal?: number;
  estoquePositivo?: boolean;
  custoDaData?: boolean;
}

interface Row {
  descricao: string;
  valor: string;
}

const filtroInicial: FiltrosRelatorio = {
  dataInicial: getDateFormat(new Date()),
  dataFinal: getDateFormat(new Date()),
  filial: 0
}

const StyledTableCell = styled(TableCell)(({ theme }) => ({
  [`&.${tableCellClasses.head}`]: {
    backgroundColor: theme.palette.common.black,
    color: theme.palette.common.white,
    border: "solid 0.5px #ccc"
  },
  [`&.${tableCellClasses.body}`]: {
    fontSize: 14,
    border: "solid 0.5px #ccc"
  },
}));

function App() {
  const [errors, setErrors] = React.useState([] as string[]);
  const [formData, setFormData] = React.useState(filtroInicial);
  const [formErrors, setFormErrors] = useState({} as FormErrors);
  const [rows, setRows] = React.useState([] as Row[]);
  const [loading, setLoading] = React.useState(false);

  const [openMenu, setOpenMenu] = React.useState(true);

  const handleMenu = () => {
    setOpenMenu(!openMenu);
  };

  const formService = new FormService(formData, setFormData, setErrors, setFormErrors, formErrors);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {

    event.preventDefault();
    formService.cleanErrors();
    setLoading(true);
    setRows([]);

    try {

      await formSchema.validate(formData, { abortEarly: false });
      const response = await api.get(`RelatorioDespesas/getRelatorioDespesas?dataInicial=${formData.dataInicial}&dataFinal=${formData.dataFinal}&filial=${formData.filial}`);
      if (response.status !== 200) {
        throw response.data;
      }
      setRows(response.data.body);

    } catch (err) {
      formService.handleErros(err);
    }
    setLoading(false);

  }

  const filiais = [
    { value: 0, label: "Selecione a Filial" },
    { value: 1, label: "Filial 1" },
    { value: 2, label: "Filial 2" },
    { value: 3, label: "Filial 3" },
    { value: 4, label: "Filial 4" }
  ];

  return (
    <>
      <AppLoading isLoading={loading} />
      <Paper elevation={0} sx={{ height: '100vh', display: "block", position: "absolute" }}>
        <Box sx={{ width: "100vw", display: 'flex', p: 1, alignItems: 'center', justifyContent: "center", background: "#1976d2", borderRadius: "0px", height: "42px" }}>
          <Assessment sx={{ fontSize: 24, color: "#ebebeb", mr: 2 }} />
          <Typography variant="h1" component="h1" sx={{ fontSize: 24, textAlign: 'center', color: "#000" }}>
            Relatório de Despesas
          </Typography>
        </Box>

        <Box sx={{ display: "flex", height: "calc(100vh - 42px)" }}>
          <Card sx={openMenu ?
            { display: "block", width: "300px", zIndex: 1, pl: 1, pr: 1, pt: 2, pb: 2 } :
            { display: "flex", justifyContent: "center", width: "40px", zIndex: 1, p: 2 }
          }>
            <Box sx={openMenu ?
              { display: "grid", gridTemplateColumns: "auto 1fr", width: "100%", pl: 2, pr: 2, mb: 4 } :
              { display: "flex", justifyContent: "center" }}>

              {
                openMenu ?
                  <ArrowBackIosIcon sx={{ fontSize: 32, cursor: "pointer" }} onClick={handleMenu} /> :
                  <FilterAlt sx={{ fontSize: 32, cursor: "pointer" }} onClick={handleMenu} />

              }
              <Box sx={openMenu ?
                { display: "flex", alignItems: "center", ml: 2 } :
                { display: "none" }}>
                <FilterAlt sx={{ fontSize: 32, color: "#1976d2" }} />
                <Typography variant="h2" component="h2" sx={{ fontSize: 24, textAlign: 'center', color: "#000" }}>
                  Filtros
                </Typography>
              </Box>
            </Box>
            <form onSubmit={handleSubmit} style={
              openMenu ? { display: "block" } : { display: "none" }}>
              <Box sx={{ gap: 4, m: 2, display: "flex", flexDirection: "column" }}>
                <AppDatePicker
                  label="Data Inicial*"
                  name="dataInicial"
                  value={moment(formData.dataInicial)}
                  onChange={(date) => formService.setInputValue("dataInicial", date?.format("YYYY-MM-DD"))}
                  errorMessage={formErrors['dataInicial']}
                />

                <AppDatePicker
                  label="Data Final*"
                  name="dataFinal"
                  value={moment(formData.dataFinal)}
                  onChange={(date) => formService.setInputValue("dataFinal", date?.format("YYYY-MM-DD"))}
                  errorMessage={formErrors['dataFinal']}
                />

                <AppSelect
                  name="filial"
                  label="Filial"
                  value={formData.filial}
                  onChange={(e) => { formService.setInputValue("filial", e.target.value); }}
                  options={filiais}
                  size='small'
                  fullWidth={true}
                  errorMessage={formErrors['filial']}
                />

                <AppTextField
                  name='estoqueInicial'
                  label='Estoque Inicial'
                  type='number'
                  value={formData.estoqueInicial}
                  onChange={(e) => formService.setInputValue("estoqueInicial", e.target.value)}
                  size='small'
                  fullWidth={true}
                  errorMessage={formErrors['estoqueInicial']}
                />

                <AppTextField
                  name='estoqueFinal'
                  label='Estoque Final'
                  type='number'
                  value={formData.estoqueFinal}
                  onChange={(e) => formService.setInputValue("estoqueFinal", e.target.value)}
                  size='small'
                  fullWidth={true}
                  errorMessage={formErrors['estoqueFinal']}
                />

                <Box sx={{ display: 'grid' }}>
                  <FormControlLabel
                    control={
                      <Checkbox defaultChecked name="estoquePositivo" onChange={(e) => formService.setInputValue("estoquePositivo", e.target.value)} />
                    }
                    label="Estoque Positivo" />

                  <FormControlLabel
                    control={
                      <Checkbox defaultChecked name="custoDaData" onChange={(e) => formService.setInputValue("custoDaData", e.target.value)} />
                    }
                    label="Custo da Data" />
                </Box>
                <Button variant="contained" sx={{ mt: 2 }} fullWidth={false} type='submit' >Buscar</Button>
              </Box>
            </form>
            <AppErrorList errors={errors} />
          </Card>


          {
            rows.length > 0 &&
            <Box sx={{ m: 2, display: "flex", alignItems: "center", flexDirection: "column" }}>
              <Typography variant="h5" component="h5" sx={{ fontWeight: "bold", textAlign: 'left', pl: 2, mb: 2 }}>Despesas</Typography>
              <TableContainer component={Paper}>
                <Table sx={{ minWidth: 650 }} size="small"  aria-label="simple table">
                  <TableHead>
                    <TableRow>
                      <StyledTableCell>Descrição </StyledTableCell>
                      <StyledTableCell align="right">Valor</StyledTableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {rows.map((row) => (
                      <TableRow
                        key={row.descricao}
                        sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                      >
                        <StyledTableCell component="th" scope="row"> {row.descricao} </StyledTableCell>
                        <StyledTableCell align="right">{Number(row.valor)?.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</StyledTableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            </Box>
          }
        </Box>
      </Paper>
    </>
  );
}

export default App;
