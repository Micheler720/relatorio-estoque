<?php
namespace App\Models;
use App\Models\ModelBase;
use CodeIgniter\Database\Query;

class RelatorioDespesaModel extends ModelBase {

  protected $table = 'fn2';
  protected $allowedFields  = [ 'descricao', 'valor' ];

  public function getRelatorio() {

    $mes = '02';
    $ano = '2024';
    $empresa = 12;
    $dataEstoqueInicial = '2024-05-31';
    $dataEstoqueFinal = '2024-05-31';


     // Define as variáveis no banco de dados
     $this->db->query("SET @mes := ?", '02');
     $this->db->query("SET @ano := ?", '2024');
     $this->db->query("SET @empresa := ?", 12);
     $this->db->query("SET @dataEstoqueInicial := ?", '2024-05-31');
     $this->db->query("SET @dataEstoqueFinal := ?", '2024-05-31');

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
                      IFNULL(sum(fn2_valor), 0)
                  FROM fn2 
                  WHERE fnb_cod = '2.01.001.0006' 
                  AND MONTH(fn2.fn2_emis ) = @mes
                  AND YEAR(fn2_emis) = @ano
                  AND fn2_empresa = @empresa)
                  INTO @valorCustoMercadoriaVendida;");

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
                INTO @valorSaldoBanco;");
                
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
    
     $this->db->query("SET @valorLucroBruto = @valorVendaBruta - @valorCustoMercadoriaVendida;");
     $this->db->query("SET @valorDemaisDespesas = @valorCompraRevenda + @valorTransferenciaMercadoria;");
     $this->db->query("SET @valorTotalReceitas = @valorVendaBruta;");
     $this->db->query("SET @valorLucroLiquido = @valorTotalReceitas - @valorDemaisDespesas - @valorCustoMercadoriaVendida;");
     $this->db->query("SET @percentualMarkupMedio = ((@valorVendaBruta - (@valorEstoqueInicial - @valorEstoqueFinal))/@valorDemaisDespesas) - 100;");
     $this->db->query("SET @resultadoLoja =  @valorVendaBruta + @valorEstoqueFinal - @valorCompraRevenda - @valorTransferenciaMercadoria - @valorCustoMercadoriaVendida;");
     $this->db->query("SET @valorDeducaoDespesa =  'VERIFICAR';");
     $this->db->query("SET @valorSaldoEmCaixa = 'VERIFICAR';");
     $this->db->query("SET @valorResultadoFinanceiro = @valorContasAReceber + @valorSaldoBanco - @valorContasAPagar;");

    $sql = "
          SELECT 
              'ESTOQUE INICIAL' AS descricao,
              @valorEstoqueInicial  AS valor
          UNION
          SELECT 
            'COMPRA E REVENDA' AS descricao, 
            @valorCompraRevenda AS valor
          UNION 
          SELECT 
            'COMPRA MATÉRIA PRIMA' AS descricao, 
            'FALTA' AS valor
          UNION
          SELECT 
            'TRANSFERENCIA MERCADORIA - Entrada' AS descricao, 
            @valorTransferenciaMercadoria AS valor
          UNION
          SELECT 
              'ESTOQUE FINAL' AS descricao,
              @valorEstoqueFinal AS valor
          UNION
          SELECT 
            'CUSTO MERCADORIA VENDIDA' AS descricao, 
            @valorCustoMercadoriaVendida AS valor
          UNION 
          SELECT 
              'VENDA BRUTA' AS descricao, 
              @valorVendaBruta AS valor
          UNION 
          SELECT 
            'OUTRAS RECEITAS' AS descricao, 
            'FALTA' AS valor
          UNION 
          SELECT 
            'DEDUÇÃO DE RECEITAS' AS descricao, 
            'FALTA' AS valor
          UNION 
          SELECT 
            'TOTAL RECEITAS' AS descricao, 
            @valorTotalReceitas AS valor
          UNION 
          SELECT 
            'DEMAIS DESPESAS' AS descricao, 
            @valorDemaisDespesas AS valor
          UNION 
          SELECT 
            'LUCRO BRUTO' AS descricao, 
            @valorLucroBruto AS valor
          UNION
          SELECT 
            'LUCRO LIQUIDO' AS descricao, 
            @valorLucroLiquido AS valor
          UNION
          SELECT 
            'MARKUP MEDIO' AS descricao, 
            @percentualMarkupMedio AS valor
          UNION
          SELECT 
            'DEDUCAO DESPESA' AS descricao, 
            @valorDeducaoDespesa AS valor
          UNION
          SELECT 
            'RESULTADO LOJA' AS descricao, 
            @resultadoLoja AS valor
          UNION
          SELECT 
              'DESPESAS LOJA' AS descricao, 
              @valorDespesaLoja AS valor 
          UNION
          SELECT 
              'RETIRADA SOCIOS' AS descricao, 
              @valorRetiradaSocios AS valor
          UNION
          SELECT 
              'SALDO EM CAIXA' AS descricao, 
              @valorSaldoEmCaixa AS valor
          UNION
          SELECT 
              'SALDO EM BANCOS' AS descricao, 
              @valorSaldoBanco AS valor
          UNION
          SELECT 
              'CONTAS A PAGAR' AS descricao, 
              @valorContasAPagar AS valor
          UNION
          SELECT 
              'CONTAS A RECEBER' AS descricao, 
              @valorContasAReceber  AS valor
          UNION
          SELECT 
              'RESULTADO FINANCEIRO' AS descricao,
              @valorResultadoFinanceiro  AS valor
          
          ;";

    $query = $this->db->query($sql);
    return $this->obterResultado($query);
  }

  
}
?>