<?php

namespace App\Models;

use App\Models\ModelBase;

class DREModel extends ModelBase
{

  protected $table = 'fn2';
  protected $allowedFields  = ['descricao', 'valor'];

  public function getRelatorio($mes, $ano, $empresa, $dataEstoqueInicial, $dataEstoqueFinal)
  {

    // Define as variáveis no banco de dados
    $this->db->query("SET @mes := ?", $mes);
    $this->db->query("SET @ano := ?", $ano);
    $this->db->query("SET @empresa := ?", $empresa);
    $this->db->query("SET @dataEstoqueInicial := ?", $dataEstoqueInicial);
    $this->db->query("SET @dataEstoqueFinal := ?", $dataEstoqueFinal);

    $this->db->query("
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0)
                  FROM fn2 
                  WHERE fnb_cod = '2.01.001.0001' 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @valorCompraRevenda");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(sum(fn2_valor), 0)
                  FROM fn2 
                  WHERE fnb_cod = '2.01.001.0006' 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @valorTransferenciaMercadoria;");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(sum(r.valorliq), 0) valor 
                  FROM sm_resumo_vendas_produtos r 
                  WHERE MONTH(r.data) = @mes
                  AND YEAR(r.data) = @ano
                  AND r.empresa = @empresa)
                  INTO @valorVendaBruta;");

    $this->db->query(" 
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod NOT IN ('2.01.001.0006', '2.01.001.0001', '2.01.001.0002') 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @valorDespesaLoja;");
    
    $this->db->query(" 
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod IN ('2.01.001.0002') 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @compraMateriaPrima;");

    $this->db->query("
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod = '4.01.001.0001' 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @valorRetiradaSocios;");

    $this->db->query("
                SELECT (SELECT 
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE c.cg6_caixa = 0 
                      AND f.fn5_recpag = 'E' 
                      AND MONTH(f.fn5_data ) = @mes
                      AND YEAR(f.fn5_data) = @ano
                      AND fn5_empresa = @empresa) -
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE  c.cg6_caixa = 0
                  AND f.fn5_recpag = 'S' 
                  AND MONTH(f.fn5_data ) = @mes
                  AND YEAR(f.fn5_data) = @ano
                  AND fn5_empresa = @empresa) AS valor)
                INTO @valorSaldoBanco;");

    $this->db->query("
                SELECT (SELECT 
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE c.cg6_caixa = 1 
                      AND f.fn5_recpag = 'E' 
                      AND MONTH(f.fn5_data ) = @mes
                      AND YEAR(f.fn5_data) = @ano
                      AND fn5_empresa = @empresa) -
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE  c.cg6_caixa = 1 
                  AND f.fn5_recpag = 'S' 
                  AND MONTH(f.fn5_data ) = @mes
                  AND YEAR(f.fn5_data) = @ano
                  AND fn5_empresa = @empresa) AS valor)
                INTO @valorSaldoEmCaixa;");

    $this->db->query("
                  SELECT (SELECT  
                      IFNULL(SUM(FN2_VALOR), 0) AS valor
                  FROM fn2 
                  WHERE FN2_DTBAIXA IS NULL)
                  INTO @valorContasAPagar;");

    $this->db->query("
                  SELECT (SELECT 
                      IFNULL(SUM(FN1_VALOR), 0)
                  FROM fn1 
                  WHERE FN1_DTBAIXA IS NULL)
                  INTO @valorContasAReceber;");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(SUM(es7_total), 0) AS valor
                  FROM es7 
                  WHERE es7_data = @dataEstoqueInicial)
                  INTO @valorEstoqueInicial;");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(SUM(es7_total), 0) AS valor
                  FROM es7 
                  WHERE es7_data = @dataEstoqueFinal)
                  INTO @valorEstoqueFinal;");

    $this->db->query("SET @valorCustoMercadoriaVendida = - @valorCompraRevenda +  @compraMateriaPrima + @valorTransferenciaMercadoria - @valorEstoqueFinal + @valorEstoqueInicial;");
    $this->db->query("SET @valorLucroBruto = @valorVendaBruta - @valorCustoMercadoriaVendida;");
    $this->db->query("SET @valorDemaisDespesas = @valorCompraRevenda + @valorTransferenciaMercadoria - @compraMateriaPrima;");
    $this->db->query("SET @valorTotalReceitas = @valorVendaBruta;");
    $this->db->query("SET @valorLucroLiquido = @valorTotalReceitas - @valorDemaisDespesas - @valorCustoMercadoriaVendida;");
    $this->db->query("SET @percentualMarkupMedio = (((@valorVendaBruta - (@valorEstoqueInicial - @valorEstoqueFinal))/@valorDemaisDespesas)-1)*100;");
    $this->db->query("SET @resultadoLoja =  @valorVendaBruta + @valorEstoqueFinal - @valorCompraRevenda - @valorTransferenciaMercadoria - @valorCustoMercadoriaVendida;");
    $this->db->query("SET @valorDeducaoDespesa =  0;");
    $this->db->query("SET @valorResultadoFinanceiro = @valorContasAReceber + @valorSaldoBanco - @valorContasAPagar;");

    $sql = "
          SELECT 
              'Estoque Inicial' AS descricao,
              @valorEstoqueInicial  AS valor,
              '' AS classe
          UNION
          SELECT 
            'Compra e Revenda' AS descricao, 
            @valorCompraRevenda AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Compra Matéria Prima' AS descricao, 
            @compraMateriaPrima AS valor,
            '' AS classe
          UNION
          SELECT 
            'Transferência Mercadoria - Entrada' AS descricao, 
            @valorTransferenciaMercadoria AS valor,
            '' AS classe
          UNION
          SELECT 
              'Estoque Final' AS descricao,
              @valorEstoqueFinal AS valor,
              '' AS classe
          UNION
          SELECT 
            'Custo Mercadoria Vendida' AS descricao, 
            @valorCustoMercadoriaVendida AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Venda Bruta' AS descricao, 
            @valorVendaBruta AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Outras Receitas' AS descricao, 
            0 AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Dedução de Receitas' AS descricao, 
            0 AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Total Receitas' AS descricao, 
            @valorTotalReceitas AS valor,
            'subtotal' AS classe
          UNION 
          SELECT 
            'Demais Despesas' AS descricao, 
            @valorDemaisDespesas AS valor,
            '' AS classe
          UNION 
          SELECT 
            'Lucro Bruto' AS descricao, 
            @valorLucroBruto AS valor,
            'subtotal' AS classe
          UNION
          SELECT 
            'Lucro Líquido' AS descricao, 
            @valorLucroLiquido AS valor,
            'subtotal' AS classe
          UNION
          SELECT 
            'Markup Médio %' AS descricao, 
            @percentualMarkupMedio AS valor,
            'subtotal' AS classe
          UNION
          SELECT 
            'Dedução de Despesas' AS descricao, 
            @valorDeducaoDespesa AS valor,
            '' AS classe
          UNION
          SELECT 
            'Resultado Lojas' AS descricao, 
            @resultadoLoja AS valor,
            '' AS classe
          UNION
          SELECT 
            'Despesas Loja' AS descricao, 
            @valorDespesaLoja AS valor,
            'subtotal' AS classe 
          UNION
          SELECT 
            'Retirada Sócios' AS descricao, 
            @valorRetiradaSocios AS valor,
            '' AS classe
          UNION
          SELECT 
            'Saldo em Caixa' AS descricao, 
            @valorSaldoEmCaixa AS valor,
            'subtotal' AS classe
          UNION
          SELECT 
            'Saldo em Banco' AS descricao, 
            @valorSaldoBanco AS valor,
            'subtotal' AS classe
          UNION
          SELECT 
            'Contas a Pagar' AS descricao, 
            @valorContasAPagar AS valor,
            '' AS classe
          UNION
          SELECT 
            'Contas a Receber' AS descricao, 
            @valorContasAReceber  AS valor,
            '' AS classe
          UNION
          SELECT 
            'Resultado Financeiro' AS descricao,
            @valorResultadoFinanceiro  AS valor,
            'resultado' AS classe
          
          ;";

    $query = $this->db->query($sql);
    return $this->obterResultado($query);
  }
}
