import React, { FormEvent, useEffect, useState } from 'react';
import Card from '@mui/material/Card';
import moment from 'moment';
import * as Yup from 'yup';
import { Button, FormControl, FormControlLabel, FormLabel, Paper, Radio, RadioGroup, styled, Table, TableBody, TableCell, tableCellClasses, TableContainer, TableHead, TableRow, Typography } from "@mui/material";
import { Assessment, FilterAlt } from '@mui/icons-material';
import { Box } from '@mui/material';
import ArrowBackIosIcon from '@mui/icons-material/ArrowBackIos';
import AppDatePicker from './components/AppDatePicker';
import FormService, { FormErrors } from './services/form.services';
import AppSelect, { AppSelectOption } from './components/AppSelect';
import AppLoading from './components/AppLoading';
import api from './services/api';
import { getDateFormat } from './utils/dateUtils';
import { getFormatNumber } from './utils/formatNumberUtils';
import AppErrorList from './components/AppErrorList';
import AppTextField from './components/AppTextField';
import { TipoEstoqueFinal } from './enums/tipoEstoqueFinal';


const formSchema = Yup.object({
  dataInventarioEstoqueInicial: Yup
    .date()
    .required('Obrigatório informar a data inicial do inventário.'),
  empresa: Yup
    .number()
    .required('Obrigatório informar a empresa.')
    .min(1, 'Obrigatório informar a empresa.'),
  dataInicial: Yup
    .string()
    .required('Obrigatório informar a data inicial para obter o resultado.')  
});

interface FiltrosRelatorio {
  dataInventarioEstoqueFinal?: string;
  dataInventarioEstoqueInicial: string;
  valorEstoqueFinal?: number;
  dataInicial: string;
  dataFinal: string;
  empresa: number;
  tipoEstoqueFinal: TipoEstoqueFinal;
}

interface Row {
  descricao: string;
  valor: string;
  classe: string;
  tipoValor: string;
}

const filtroInicial: FiltrosRelatorio = {
  dataInventarioEstoqueInicial: getDateFormat(new Date()),
  dataInventarioEstoqueFinal: getDateFormat(new Date()),
  dataInicial: getDateFormat(new Date()),
  dataFinal: getDateFormat(new Date()),
  tipoEstoqueFinal: TipoEstoqueFinal.Data,
  empresa: 0
}

const StyledTableRow = styled(TableRow)(() => ({
}));

const StyledTableCell = styled(TableCell)(({ theme }) => ({
  [`&.${tableCellClasses.head}`]: {
    backgroundColor: "#1976d2",
    color: theme.palette.common.white,
    fontWeight: "bold",
    textAlign: "center"
  },
  [`&.${tableCellClasses.body}`]: {
    fontSize: 14,
  },
}));

const StyledTableSubtotal = styled(TableCell)(({ theme }) => ({
  [`&.${tableCellClasses.body}`]: {
    fontSize: 14,
    fontWeight: "bold",
    backgroundColor: theme.palette.action.hover,
  },
}));

const StyledTableResultado = styled(TableCell)(({ theme }) => ({
  [`&.${tableCellClasses.body}`]: {
    fontSize: 14,
    fontWeight: "bold",
    backgroundColor: "#1976d2",
    color: theme.palette.common.white,
  },
}));

function App() {
  const [errors, setErrors] = React.useState([] as string[]);
  const [filiais, setFiliais] = React.useState([] as AppSelectOption[]);
  const [title, setTitle] = React.useState("");
  const [formData, setFormData] = React.useState(filtroInicial);
  const [formErrors, setFormErrors] = useState({} as FormErrors);
  const [rows, setRows] = React.useState([] as Row[]);
  const [loading, setLoading] = React.useState(false);

  const [openMenu, setOpenMenu] = React.useState(true);

  const handleMenu = () => {
    setOpenMenu(!openMenu);
  };

  const formService = new FormService(formData, setFormData, setErrors, setFormErrors, formErrors);

  const handleGetEmpresas = async () => {
    setLoading(true);
    try {
      const response = await api.get(`Empresas/get`);
      if (response.status !== 200) {
        throw response.data;
      }

      let opcoes = [] as AppSelectOption[];

      opcoes.push({ value: 0, label: "Selecione uma empresa" })

      response.data.body.forEach((element: any) => {
        opcoes.push({ value: element.filial, label: element.id })
      });

      setFiliais(opcoes);


    } catch (err) {
      formService.handleErros(err);
    }

    setLoading(false);

  }

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    formService.cleanErrors();
    setLoading(true);
    setRows([]);

    try {

      console.log(formData);

      await formSchema.validate(formData, { abortEarly: false });

      const ano = formData.dataInicial.substring(0, 4);
      const mes = formData.dataInicial.substring(5, 7);

      const { dataFinal, dataInicial, dataInventarioEstoqueFinal, dataInventarioEstoqueInicial, empresa, tipoEstoqueFinal, valorEstoqueFinal } = formData;


      const response = await api.get(`DRE/getDRE?`
        + `dataInventarioEstoqueInicial=${dataInventarioEstoqueInicial}`
        + `&dataInventarioEstoqueFinal=${dataInventarioEstoqueFinal}`
        + `&tipoEstoqueFinal=${tipoEstoqueFinal}`
        + `&valorEstoqueFinal=${valorEstoqueFinal}`
        + `&empresa=${empresa}&dataInicial=${dataInicial}&dataFinal=${dataFinal}`);

      if (response.status !== 200) {
        throw response.data;
      }
      setTitle(`DRE ${mes}-${ano}`);
      setRows(response.data.body);


    } catch (err) {
      formService.handleErros(err);
    }
    setLoading(false);

  }

  useEffect(() => {
    handleGetEmpresas();
  }, [])

  return (
    <>
      <AppLoading isLoading={loading} />
      <Paper elevation={0} sx={{ height: '100vh', display: "block", position: "absolute" }}>
        <Box sx={{ minWidth: "100vw", display: 'flex', p: 1, alignItems: 'center', justifyContent: "center", background: "#1976d2", borderRadius: "0px", height: "42px" }}>
          <Assessment sx={{ fontSize: 24, color: "#ebebeb", mr: 2 }} />
          <Typography variant="h1" component="h1" sx={{ fontSize: 24, textAlign: 'center', color: "#000" }}>
            DRE
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
                  label="Data Inicial"
                  name="dataInicial"
                  value={moment(formData.dataInicial)}
                  format="DD-MM-YYYY"
                  onChange={(date) => { formService.setInputValue("dataInicial", date?.format("YYYY-MM-DD")); }}
                  errorMessage={formErrors['dataInicial']}
                />

                <AppDatePicker
                  label="Data Final"
                  name="dataFinal"
                  value={moment(formData.dataFinal)}
                  format="DD-MM-YYYY"
                  onChange={(date) => { formService.setInputValue("dataFinal", date?.format("YYYY-MM-DD")); }}
                  errorMessage={formErrors['dataFinal']}
                />

                <AppSelect
                  name="empresa"
                  label="Filial*"
                  value={formData.empresa}
                  onChange={(e) => { formService.setInputValue("empresa", e.target.value); }}
                  options={filiais}
                  size='small'
                  fullWidth={true}
                  errorMessage={formErrors['empresa']}
                />

                <AppDatePicker
                  label="Data inventário Inicial*"
                  name="dataInventarioEstoqueInicial"
                  value={moment(formData.dataInventarioEstoqueInicial)}
                  onChange={(date) => formService.setInputValue("dataInventarioEstoqueInicial", date?.format("YYYY-MM-DD"))}
                  errorMessage={formErrors['dataInventarioEstoqueInicial']}
                />
                <FormControl>
                  <FormLabel id="tipoEstoqueFinal">Tipo Estoque Final</FormLabel>
                  <RadioGroup
                    row
                    name="tipoEstoqueFinal"
                    onChange={(event) =>{formService.setInputValue("tipoEstoqueFinal", Number(event.target.value))}}
                  >
                    <FormControlLabel checked={formData.tipoEstoqueFinal === TipoEstoqueFinal.Data} value={TipoEstoqueFinal.Data} control={<Radio />} label="Data" />
                    <FormControlLabel checked={formData.tipoEstoqueFinal === TipoEstoqueFinal.Valor} value={TipoEstoqueFinal.Valor} control={<Radio />} label="Valor" />
                  </RadioGroup>
                </FormControl>

                {formData.tipoEstoqueFinal === TipoEstoqueFinal.Data &&
                  <AppDatePicker
                    label="Data inventário Final*"
                    name="dataInventarioEstoqueFinal"
                    value={moment(formData.dataInventarioEstoqueFinal)}
                    onChange={(date) => formService.setInputValue("dataInventarioEstoqueFinal", date?.format("YYYY-MM-DD"))}
                    errorMessage={formErrors['dataInventarioEstoqueFinal']}
                  />
                }

                {formData.tipoEstoqueFinal === TipoEstoqueFinal.Valor &&
                  <AppTextField 
                    label="Valor estoque final"
                    name="valorEstoqueFinal"
                    type='number'
                    value={formData.valorEstoqueFinal}
                    onChange={(event) => formService.setInputValue("valorEstoqueFinal", event.target.value)}
                    errorMessage={formErrors['valorEstoqueFinal']}
                    fullWidth
                  />
                }
                <Button variant="contained" sx={{ mt: 2 }} fullWidth={false} type='submit' >Buscar</Button>
              </Box>
            </form>
            <AppErrorList errors={errors} />
          </Card>


          {
            rows.length > 0 &&
            <Box sx={{ m: 2, ml: 5, display: "flex", alignItems: "center", flexDirection: "column" }}>
              <Typography variant="h5" component="h5" sx={{ fontWeight: "bold", textAlign: 'left', pl: 2, mb: 2 }}>{title}</Typography>
              <TableContainer component={Paper}>
                <Table sx={{ minWidth: 650 }} stickyHeader size="small" aria-label="simple table">
                  <TableHead>
                    <TableRow>
                      <StyledTableCell>Descrição </StyledTableCell>
                      <StyledTableCell align="right">Valor</StyledTableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {rows.map((row) => (
                      <StyledTableRow
                        key={row.descricao}
                        sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                      >
                        {row.classe === "subtotal" &&
                          <>
                            <StyledTableSubtotal component="th" scope="row"> {row.descricao} </StyledTableSubtotal>
                            <StyledTableSubtotal align="right">{getFormatNumber(row.valor, row.tipoValor)}</StyledTableSubtotal>
                          </>
                        }
                        {
                          row.classe === "resultado" &&
                          <>
                            <StyledTableResultado component="th" scope="row"> {row.descricao} </StyledTableResultado>
                            <StyledTableResultado align="right">{getFormatNumber(row.valor, row.tipoValor)}</StyledTableResultado>
                          </>
                        }
                        {
                          row.classe === "" &&
                          <>
                            <StyledTableCell component="th" scope="row"> {row.descricao} </StyledTableCell>
                            <StyledTableCell align="right">{getFormatNumber(row.valor, row.tipoValor)}</StyledTableCell>
                          </>
                        }
                      </StyledTableRow>
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
