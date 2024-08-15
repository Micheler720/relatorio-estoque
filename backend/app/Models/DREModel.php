<?php

namespace App\Models;

use App\Models\ModelBase;
use App\Enums\TipoValor;
use App\Enums\TipoEstoqueFinal;

class DREModel extends ModelBase
{

  protected $table = 'fn2';
  protected $allowedFields  = ['descricao', 'valor'];

  public function getRelatorio($dataInicial, $dataFinal, $empresa, $dataEstoqueInicial, $dataEstoqueFinal, $tipoEstoqueFinal, $valorEstoqueFinal)
  {

    // Define as variáveis no banco de dados
    $this->db->query("SET @dataInicial := ?", $dataInicial);
    $this->db->query("SET @dataFinal := ?", $dataFinal);
    $this->db->query("SET @empresa := ?", $empresa);
    $this->db->query("SET @dataEstoqueInicial := ?", $dataEstoqueInicial);
    $this->db->query("SET @dataEstoqueFinal := ?", $dataEstoqueFinal);

    
    $this->obterValorEstoqueFinal($tipoEstoqueFinal, $valorEstoqueFinal);
    
    $this->db->query("
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0)
                  FROM fn2 
                  WHERE fnb_cod = '2.01.001.0001' 
                  AND fn2.fn2_emis  >= @dataInicial
                  AND fn2_emis <= @dataFinal
                  AND fn2_empresa = @empresa)
                  INTO @valorCompraRevenda");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(sum(fn2_valor), 0)
                  FROM fn2 
                  WHERE fnb_cod = '2.01.001.0006' 
                  AND fn2.fn2_emis  >= @dataInicial
                  AND fn2_emis <= @dataFinal
                  AND fn2_empresa = @empresa)
                  INTO @valorTransferenciaMercadoria;");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(sum(r.valorliq), 0) valor 
                  FROM sm_resumo_vendas_produtos r 
                  WHERE r.data >= @dataInicial
                  AND r.data <= @dataFinal
                  AND r.empresa = @empresa)
                  INTO @valorVendaBruta;");

    $this->db->query(" 
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod NOT IN ('2.01.001.0006', '2.01.001.0001', '2.01.001.0002') 
                  AND fn2.fn2_emis  >= @dataInicial
                  AND fn2_emis <= @dataFinal
                  AND fn2_empresa = @empresa)
                  INTO @valorDespesaLoja;");

    $this->db->query("
                  SELECT (SELECT
                      IFNULL(SUM(es7_total), 0) AS valor
                  FROM es7 
                  WHERE es7_data = @dataEstoqueInicial)
                  INTO @valorEstoqueInicial;");

    $this->db->query(" 
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod IN ('3.05.003.0003', '4.01.001.0001') 
                  AND fn2.fn2_emis  >= @dataInicial
                  AND fn2_emis <= @dataFinal
                  AND fn2_empresa = @empresa)
                  INTO @valorDeducaoDespesa;");

    $this->db->query("
                  SELECT (SELECT 
                      IFNULL(sum(fn2_valor), 0) valor  
                  FROM fn2 
                  WHERE fnb_cod = '4.01.001.0001' 
                  AND fn2.fn2_emis  >= @dataInicial
                  AND fn2_emis <= @dataFinal
                  AND fn2_empresa = @empresa)
                  INTO @valorRetiradaSocios;");

    $this->db->query("
                  SELECT (SELECT 
                    (SELECT 
                        IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                    FROM fn5 f 
                    INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                    WHERE c.cg6_cod = 3
                        AND f.fn5_recpag = 'E' 
                        AND f.fn5_data <= @dataFinal
                        AND fn5_empresa = @empresa) -
                    (SELECT 
                        IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                    FROM fn5 f 
                    INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                    WHERE  c.cg6_cod = 3
                    AND f.fn5_recpag = 'S' 
                    AND f.fn5_data <= @dataFinal
                    AND fn5_empresa = @empresa) AS valor)
                  INTO @valorSaldoTesouraria;");

    $this->db->query("
                SELECT (SELECT 
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE c.cg6_cod = 4
                      AND f.fn5_recpag = 'E' 
                      AND f.fn5_data <= @dataFinal
                      AND fn5_empresa = @empresa) -
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE  c.cg6_cod = 4
                  AND f.fn5_recpag = 'S' 
                  AND f.fn5_data <= @dataFinal
                  AND fn5_empresa = @empresa) AS valor)
                INTO @valorSaldoCaixaPDV;");

    $this->db->query("
                SELECT (SELECT 
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE c.cg6_cod = 2
                      AND f.fn5_recpag = 'E' 
                      AND f.fn5_data <= @dataFinal
                      AND fn5_empresa = @empresa) -
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE  c.cg6_cod = 2
                  AND f.fn5_recpag = 'S' 
                  AND f.fn5_data <= @dataFinal
                  AND fn5_empresa = @empresa) AS valor)
                INTO @valorSaldoSicoob;");

    $this->db->query("
                SELECT (SELECT 
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Entrada' 
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE c.cg6_cod = 5
                      AND f.fn5_recpag = 'E' 
                      AND f.fn5_data <= @dataFinal
                      AND fn5_empresa = @empresa) -
                  (SELECT 
                      IFNULL(SUM(f.fn5_valor), 0) AS 'Saida '
                  FROM fn5 f 
                  INNER JOIN cg6 c ON c.cg6_cod = f.cg6_cod
                  WHERE  c.cg6_cod = 5
                  AND f.fn5_recpag = 'S' 
                  AND f.fn5_data <= @dataFinal
                  AND fn5_empresa = @empresa) AS valor)
		  INTO @valorSaldoPIX;");

    $this->db->query("
                  SELECT (
                    SELECT IFNULL (SUM(fn1_valor),0) 
                    FROM fn1 f INNER JOIN cg1 c 	
                    ON c.cg1_cod = f.cg1_cod
                    WHERE c.cg1_classe = 1 and f.fn1_dtbaixa IS NULL	
                    AND fn1_emissao <= @dataFinal
                          AND fn1_empresa = @empresa) 
                    +
                    (SELECT IFNULL(SUM(fn1_valor),0) 
                    FROM fn1 f INNER JOIN cg1 c
                    ON c.cg1_cod = f.cg1_cod 
                    WHERE c.cg1_classe <> 1 AND 
                    fn1_emissao <= @dataFinal
                          AND fn1_dtbaixa > @dataFinal
                          AND fn1_empresa = @empresa) 
                    INTO @valorContasAReceberClientes;");

    $this->db->query("
                    SELECT (
                      SELECT IFNULL (SUM(fn1_valor),0) 
                      FROM fn1 f INNER JOIN cg1 c 	
                      ON c.cg1_cod = f.cg1_cod
                      WHERE c.cg1_classe = 1 and f.fn1_dtbaixa IS NULL	
                      AND fn1_emissao <= @dataFinal
                            AND fn1_empresa = @empresa) 
                      +
                      (SELECT IFNULL(SUM(fn1_valor),0) 
                      FROM fn1 f INNER JOIN cg1 c
                      ON c.cg1_cod = f.cg1_cod 
                      WHERE c.cg1_classe = 1 
                          AND fn1_emissao <= @dataFinal
                          AND fn1_dtbaixa > @dataFinal
                          AND fn1_empresa = @empresa) 
                      INTO @valorContasAReceberCartao;");

    $this->db->query("
                      SELECT (
          SELECT IFNULL (SUM(fn2_valor),0) 
          FROM fn2 	
          WHERE fn2_dtbaixa IS NULL
           AND fn2_emis <= @dataFinal
                AND fn2_empresa = @empresa) 
          +
          (SELECT IFNULL(SUM(fn2_valor),0) 
          FROM fn2 
          WHERE
          fn2_emis >= @dataFinal
                AND fn2_dtbaixa > @dataFinal
                AND fn2_empresa = @empresa) 
          INTO @valorContasAPagar;");

    //$this->db->query("SET @valorCustoMercadoriaVendida = @valorCompraRevenda +  @compraMateriaPrima + @valorTransferenciaMercadoria - @valorEstoqueFinal + @valorEstoqueInicial;");
    //$this->db->query("SET @valorLucroBruto = @valorVendaBruta - @valorCustoMercadoriaVendida;");
    //$this->db->query("SET @valorDemaisDespesas = @valorCompraRevenda + @valorTransferenciaMercadoria - @compraMateriaPrima;");
    //$this->db->query("SET @valorTotalReceitas = @valorVendaBruta;");
    //$this->db->query("SET @valorLucroLiquido = @valorTotalReceitas - @valorDemaisDespesas - @valorCustoMercadoriaVendida;");
    $this->db->query("SET @valorResultadoFinanceiro = @valorSaldoTesouraria + @valorSaldoCaixaPDV + @valorSaldoSicoob + @valorSaldoPIX + @valorContasAReceberClientes + @valorContasAReceberCartao  - @valorContasAPagar;");
    $this->db->query("SET @totalsaldo = @valorSaldoTesouraria + @valorSaldoCaixaPDV + @valorSaldoSicoob + @valorSaldoPIX;");
    $this->db->query("SET @valorRFEF = @valorResultadoFinanceiro + @valorEstoqueFinal  ;");
    $this->db->query("SET @diferencaEstoque = @valorEstoqueFinal - @valorEstoqueInicial;");
    $this->db->query("SET @somaTotalMercadoria = @valorCompraRevenda + @valorTransferenciaMercadoria;");
    $this->db->query("SET @lucroTotal = @valorVendaBruta - @somaTotalMercadoria;");
    $this->db->query("SET @calculoMarkup = @valorEstoqueFinal- @valorEstoqueInicial; ");
    $this->db->query("SET @lucroPer = (@lucroTotal / @somaTotalMercadoria)*100;");
    $this->db->query("SET @totalDespesas = @valorDespesaLoja - @valorDeducaoDespesa");
    // $this->db->query("SET @valorResultadoLoja = @valorVendaBruta + @valorEstoqueFinal - @totalDespesas - @valorCompraRevenda - @valorEstoqueInicial;");
    $this->db->query("SET @totalAReceber = @valorContasAReceberClientes + @valorContasAReceberCartao;");
    $this->db->query("SET @perMarkup = (((IF(@calculoMarkup < 0, -@calculoMarkup, @calculoMarkup) + @valorVendaBruta) / @somaTotalMercadoria) - 1) * 100;");
    $this->db->query("SET @resultadoLoja =  @valorVendaBruta + @valorEstoqueFinal - @totalDespesas - @valorCompraRevenda - @valorTransferenciaMercadoria - @valorEstoqueInicial;");
    $sql = "
          SELECT 
              'Estoque Inicial' AS descricao,
              @valorEstoqueInicial  AS valor,
              '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Estoque Final' AS descricao,
              @valorEstoqueFinal AS valor,
              '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Diferença Estoque' AS descricao, 
            @diferencaEstoque AS valor,
            'subtotal' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Compra e Revenda' AS descricao, 
            @valorCompraRevenda AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
              'Transferencia Mercadoria Entrada' AS descricao,
              @valorTransferenciaMercadoria AS valor,
              '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Total Compra Mercadoria' AS descricao, 
            @somaTotalMercadoria AS valor,
            'subtotal' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Venda' AS descricao, 
            @valorVendaBruta AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Lucro' AS descricao, 
            @lucroTotal AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Lucro %' AS descricao, 
            @lucroPer AS valor,
            '' AS classe," .
      TipoValor::percentual() . " AS tipoValor"
      . " UNION
          SELECT 
            'Despesas Loja' AS descricao, 
            @valorDespesaLoja AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Dedução de Despesas' AS descricao, 
            @valorDeducaoDespesa AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Total Despesas' AS descricao, 
            @totalDespesas AS valor,
            'subtotal' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Retirada Sócios' AS descricao, 
            @valorRetiradaSocios AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Markup % - Conferir a conta***' AS descricao, 
            @perMarkup AS valor,
            'resultado' AS classe," .
      TipoValor::percentual() . " AS tipoValor"
      . " UNION
          SELECT 
            'Resultado Loja' AS descricao, 
            @resultadoLoja AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Saldo Tesouraria' AS descricao, 
            @valorSaldoTesouraria AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Saldo Troco Loja' AS descricao, 
            @valorSaldoCaixaPDV AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Saldo SICOOB' AS descricao, 
            @valorSaldoSicoob AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Saldo PIX' AS descricao, 
            @valorSaldoPIX AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Total Saldo Bancario >>>> ' AS descricao, 
            @totalsaldo AS valor,
            'resultado' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Clientes a Receber' AS descricao, 
            @valorContasAReceberClientes AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Cartões a Receber' AS descricao, 
            @valorContasAReceberCartao AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Total a Receber' AS descricao, 
            @totalAReceber  AS valor,
            'subtotal' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Contas a Pagar' AS descricao,
            @valorContasAPagar  AS valor,
            '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT 
            'Resultado Financeiro' AS descricao,
            @valorResultadoFinanceiro  AS valor,
            'resultado' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor"
      . " UNION
          SELECT
           'RF + EF' AS descricao,
           @valorRFEF AS valor,
           '' AS classe," .
      TipoValor::dinheiro() . " AS tipoValor";

    $query = $this->db->query($sql);
    return $this->obterResultado($query);
  }

  private function obterValorEstoqueFinal($tipoEstoqueFinal, $valorEstoqueFinal) {
    if($tipoEstoqueFinal == TipoEstoqueFinal::data()) {
      $this->db->query("
              SELECT (SELECT
                  IFNULL(SUM(es7_total), 0) AS valor
              FROM es7 
              WHERE es7_data = @dataEstoqueFinal)
              INTO @valorEstoqueFinal;");
      return;
    }

    $this->db->query("SET @valorEstoqueFinal := ?", $valorEstoqueFinal);    
  }
}
