<?php
use App\Models\Contract;

$mesesPt = [1=>'janeiro',2=>'fevereiro',3=>'março',4=>'abril',5=>'maio',6=>'junho',
            7=>'julho',8=>'agosto',9=>'setembro',10=>'outubro',11=>'novembro',12=>'dezembro'];
$dataExtenso = (int)date('d') . ' de ' . $mesesPt[(int)date('n')] . ' de ' . date('Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Contrato #<?= (int)$contract->id ?> — <?= htmlspecialchars($contract->patientName ?? '') ?></title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: 'Times New Roman', Georgia, serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 32px;
    color: #111;
    line-height: 1.55;
    font-size: 14px;
    text-align: justify;
  }
  h1 { font-size: 16px; text-align: center; margin-bottom: 24px; text-transform: uppercase; }
  p { margin: 0 0 12px 0; }
  strong { font-weight: bold; }
  .clausula { margin: 18px 0 6px 0; }
  .paragrafo { margin: 8px 0 8px 18px; }
  table.parcelas { width: 100%; border-collapse: collapse; margin: 10px 0 16px 0; font-size: 13px; }
  table.parcelas th, table.parcelas td { border: 1px solid #999; padding: 5px 8px; text-align: left; }
  table.parcelas th { background: #eee; }
  ul.preco-rescisao { margin: 6px 0 14px 18px; padding: 0; }
  ul.preco-rescisao li { margin-bottom: 2px; }
  .assinaturas { margin-top: 60px; }
  .linha-assinatura { border-top: 1px solid #000; width: 340px; margin: 50px auto 4px auto; text-align: center; padding-top: 4px; }
  .centro { text-align: center; }
  .toolbar { max-width: 800px; margin: 16px auto; display: flex; gap: 10px; justify-content: flex-end; font-family: Arial, sans-serif; }
  .toolbar button, .toolbar a {
    font-size: 14px; padding: 8px 16px; border-radius: 6px;
    border: 1px solid #999; background: #f5f5f5; color: #222; text-decoration: none; cursor: pointer;
  }
  @media print {
    .toolbar { display: none; }
    body { padding: 0; }
  }
</style>
</head>
<body>

<div class="toolbar">
  <a href="<?= BASE_URL ?>/?controller=contract&action=index">Voltar</a>
  <a href="<?= BASE_URL ?>/?controller=contract&action=downloadDocx&id=<?= (int)$contract->id ?>">Baixar Word</a>
  <button onclick="window.print()">Imprimir</button>
</div>

<h1>Contrato de Prestação de Serviços Estéticos</h1>

<p>
  <strong>NATÁLIA BALBINOTTI ESTÉTICA INTEGRATIVA LTDA</strong>, pessoa jurídica de direito privado, inscrita no
  CNPJ/MF sob o nº 47.249.775/0001-69, estabelecida na Rua Paranaguá nº 334, Lj. 05, Centro, CEP 86.020-030,
  no Município de Londrina, Estado do Paraná, por sua representante legal abaixo identificada,
  doravante denominada simplesmente <strong>CONTRATADA</strong>.
</p>

<p>
  <strong><?= htmlspecialchars(strtoupper($contract->patientName ?? '')) ?></strong>,
  <?= htmlspecialchars($contract->patientNationality ?: '[nacionalidade não informada]') ?>,
  <?= htmlspecialchars($contract->patientMaritalStatus ?: '[estado civil não informado]') ?>,
  <?= htmlspecialchars($contract->patientOccupation ?: '[profissão não informada]') ?>,
  portador(a) do RG nº <?= htmlspecialchars($contract->patientRg ?: '[RG não informado]') ?>,
  inscrito(a) no CPF sob o nº <?= htmlspecialchars($contract->patientCpf ?: '[CPF não informado]') ?>,
  residente e domiciliado(a) na <?= htmlspecialchars($contract->patientAddress ?: '[endereço não informado]') ?>,
  CEP <?= htmlspecialchars($contract->patientCep ?: '[CEP não informado]') ?>,
  no Município de Londrina, Estado do Paraná, doravante denominada simplesmente <strong>CONTRATANTE</strong>.
</p>

<p>
  Pelo presente instrumento, ajustam e contratam a Prestação de Serviços Estéticos que será regida pelos
  termos e condições estabelecidas nas CLÁUSULAS a seguir enumeradas.
</p>

<p class="clausula"><strong>CLÁUSULA 1ª – OBJETO DO CONTRATO:</strong> O presente contrato tem como objeto a
  prestação de serviços estéticos compreendidos em <strong><?= htmlspecialchars(strtoupper($contract->serviceTypeName ?? '')) ?></strong> (TRATAMENTO).
</p>

<p class="clausula"><strong>CLÁUSULA 2ª – INVESTIMENTO E FORMA DE PAGAMENTO:</strong> Como pagamento da prestação
  dos serviços especificados na Cláusula 1ª, se obriga a CONTRATANTE ao pagamento à CONTRATADA do valor total de
  <strong>R$ <?= number_format($contract->totalValue, 2, ',', '.') ?></strong>, na forma de
  <strong><?= Contract::paymentConditionLabel($contract->paymentCondition) ?></strong>, dividido em
  <strong><?= (int)$contract->installments ?></strong> parcela(s), conforme discriminado abaixo:
</p>

<table class="parcelas">
  <tr><th>Parcela</th><th>Vencimento</th><th>Valor</th></tr>
  <?php $n = count($installments); ?>
  <?php foreach ($installments as $inst): ?>
    <tr>
      <td><?= (int)$inst->installmentNumber ?>/<?= $n ?></td>
      <td><?= date('d/m/Y', strtotime($inst->dueDate)) ?></td>
      <td>R$ <?= number_format($inst->amount, 2, ',', '.') ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<p class="paragrafo"><strong>PARÁGRAFO PRIMEIRO:</strong> O atraso no pagamento de quaisquer importâncias devidas,
  vencidas e não pagas na época em que forem exigíveis por força da Cláusula 2ª, implicará automaticamente na mora
  e ações de cobrança, ficando o débito sujeito, do vencimento ao efetivo pagamento, a correção monetária pelo
  INPC/IBGE, incidência de juros moratório de 1% ao mês, multa de 2%, bem como a incidência de honorários
  advocatícios em razão da cobrança extrajudicial, limitados a 10% (dez por cento) do valor total devido.
</p>
<p class="paragrafo"><strong>PARÁGRAFO SEGUNDO:</strong> Em caso de conclusão do tratamento por parte da
  CONTRATADA, fica firmado que o inadimplemento de 2 (duas) parcelas ensejará a antecipação do vencimento das
  demais, podendo a CONTRATADA exigir a totalidade dos valores que restarem devidos à época, mais correção
  monetária, juros moratórios e multa nos termos do parágrafo anterior.
</p>

<p class="clausula"><strong>CLÁUSULA 3ª – RESULTADOS E POSSÍVEIS CONSEQUÊNCIAS:</strong> A CONTRATANTE, após
  receber todas as orientações da profissional responsável pelo desenvolver do procedimento estético, manifesta
  total ciência acerca dos resultados e consequências possíveis.
</p>

<p class="clausula"><strong>CLÁUSULA 4ª – DOS EXAMES NECESSÁRIOS:</strong> Antes, durante e após o término do
  procedimento estético, poderá a CONTRATADA exigir da CONTRATANTE exames de sangue e testes de cetose, devendo a
  CONTRATANTE arcar com os custos decorrentes da realização dos exames/testes.
</p>

<p class="clausula"><strong>CLÁUSULA 5ª – DAS OBRIGAÇÕES DA CONTRATANTE:</strong> A CONTRATANTE terá as
  obrigações de:</p>
<p class="paragrafo">
  a) Realizar os pagamentos em observância às estipulações estabelecidas na Cláusula 2ª do presente contrato;<br>
  b) Manter os dados pessoais sempre atualizados, informando sempre qualquer alteração;<br>
  c) Seguir todas as orientações estabelecidas no plano de tratamento;<br>
  d) Manter a pontualidade nas sessões.
</p>
<p class="paragrafo"><strong>PARÁGRAFO PRIMEIRO:</strong> Na hipótese de a CONTRATANTE não comparecer na sessão
  previamente agendada pela CONTRATADA, esta manifesta ciência de que somente será possível reagendar a referida
  sessão caso comunique a CONTRATADA em até 3 (três) horas que antecedem o horário marcado para sessão.
</p>
<p class="paragrafo"><strong>PARÁGRAFO SEGUNDO:</strong> As 3 (três) horas estabelecidas no parágrafo anterior
  deverão estar compreendidas dentro do horário de funcionamento da clínica CONTRATADA, sob pena de não ser
  reconhecida.
</p>

<p class="clausula"><strong>CLÁUSULA 6ª – DAS OBRIGAÇÕES DA CONTRATADA:</strong> A CONTRATADA terá o dever de:</p>
<p class="paragrafo">
  a) Exercer as atividades descritas na Cláusula 1ª do presente contrato;<br>
  b) Garantir a execução dos serviços na sua totalidade;<br>
  c) Tomar todas as medidas de segurança disponíveis na clínica onde for feito o tratamento, objetivando reduzir
  ao mínimo possível os riscos;<br>
  d) Usar de todos os meios técnicos e científicos à sua disposição para tentar atingir o resultado desejado pela
  CONTRATANTE.
</p>
<p class="paragrafo"><strong>PARÁGRAFO ÚNICO:</strong> A CONTRATADA declara possuir os conhecimentos técnicos e
  experiência necessária para a boa execução do contrato, responsabilizando-se por eventuais danos causados à
  CONTRATANTE por vícios na prestação de serviço.
</p>
<p class="paragrafo"><strong>PARÁGRAFO ÚNICO:</strong> A CONTRATANTE declara ter consciência de que deve acatar e
  seguir as determinações que serão fornecidas (oralmente ou por escrito), pois reconhece que se não fizer sua
  parte, poderá comprometer em parte ou no todo o trabalho da profissional responsável pelo desenvolvimento do
  procedimento clínico, além de pôr em risco sua saúde, bem-estar ou, ainda, ocasionar sequelas temporárias ou
  permanentes.
</p>

<p class="clausula"><strong>CLÁUSULA 7ª – DA GARANTIA DE RESULTADO:</strong> A CONTRATANTE reconhece que cada
  organismo reage de uma forma específica, fato este que torna impossível prever matematicamente um resultado
  para todo e qualquer procedimento estético, razão pela qual aceita o fato de que não lhe é dado garantia de
  resultado, tais como: percentual de melhora, de aparência ou de permanência dos resultados atingidos.
</p>

<p class="clausula"><strong>CLÁUSULA 8ª – RESCISÃO</strong></p>
<p class="paragrafo"><strong>PARÁGRAFO PRIMEIRO:</strong> O abandono do tratamento antes do término estipulado
  neste contrato, por parte da CONTRATANTE, caracterizado pela ausência em 2 (duas) sessões consecutivas sem
  apresentação de justificativa, acarretará automaticamente a rescisão deste contrato.
</p>
<p class="paragrafo"><strong>PARÁGRAFO SEGUNDO:</strong> Na hipótese do contrato ser rescindido até o momento em
  que cumprido até 30% (trinta por cento) do tratamento estético, será devido uma multa de 50% (cinquenta por
  cento) calculado sobre o saldo remanescente do contrato, enquanto que na hipótese do contrato ser rescindido até
  o momento em que cumprido mais de 30% (trinta por cento) do tratamento estético, será devido uma multa de 15%
  (quinze por cento) do valor total do contrato.
</p>
<p class="paragrafo"><strong>PARÁGRAFO TERCEIRO:</strong> Independente da forma de pagamento optada no ato da
  contratação, no momento em que a CONTRATANTE optar pela rescisão contratual, deverá arcar com os valores devidos
  pela prestação do serviço já realizado pela CONTRATADA, bem como suportar a multa contratual estabelecida no
  <em>caput</em> da cláusula em questão.
</p>
<p class="paragrafo"><strong>PARÁGRAFO QUARTO:</strong> na hipótese de rescisão contratual, a CONTRATANTE pagará à
  CONTRATADA os seguintes valores pelos serviços já realizados:</p>
<ul class="preco-rescisao">
  <li>CONSULTA: R$ 350,00.</li>
  <li>SESSÃO DETOX: R$ 490,00;</li>
  <li>SESSÃO FANTASTIC SLIM: R$ 600,00;</li>
  <li>SESSÃO APARELHO: R$ 650,00;</li>
  <li>ACOMPANHAMENTO DIÁRIO NO GRUPO EXCLUSIVO DURANTE O PERÍODO DO TRATAMENTO: R$ 1.000,00;</li>
  <li>ELABORAÇÃO DE CARDÁPIO SUGESTIVO: R$ 650,00;</li>
  <li>SESSÃO MENTORIA LIFE COACH: R$ 450,00;</li>
  <li>SESSÃO PRÉ-CRIO: R$ 500,00;</li>
  <li>SESSÃO CRIOLIPÓLISE: R$ 3.800,00;</li>
  <li>SESSÃO PÓS-CRIO: R$ 650,00;</li>
  <li>MICRO AGULHAMENTO: R$ 600,00;</li>
  <li>SESSÃO FACIAL: R$ 600,00.</li>
</ul>

<p class="clausula"><strong>CLÁUSULA 9ª – CESSÃO DO USO E EXPLORAÇÃO DE IMAGEM:</strong> Desde que exista sigilo
  sobre a identidade, a CONTRATANTE autoriza a CONTRATADA a utilizar as imagens do "antes e depois" do resultado
  obtido com o procedimento estético realizado, nos materiais publicitários de divulgação da clínica estética,
  sendo o prazo de utilização indeterminado.
</p>
<p class="paragrafo"><strong>PARÁGRAFO ÚNICO:</strong> Na hipótese da CONTRATANTE ser contrária à cessão de
  direitos de imagem, deverá ela, por meio de manifestação escrita, desautorizar a clínica CONTRATADA em até 15
  (quinze) dias após o término do procedimento realizado, sob pena de ocorrer o consentimento pleno.
</p>

<p class="clausula"><strong>CLÁUSULA 10ª – DISPOSIÇÕES GERAIS:</strong> A CONTRATANTE autoriza a realização de
  todos e quaisquer procedimentos clínicos que forem julgados, por ela e/ou por sua equipe, como necessários à
  obtenção de melhores resultados no tratamento clínico.
</p>
<p class="paragrafo"><strong>PARÁGRAFO PRIMEIRO:</strong> A CONTRATANTE declara ter recebido, previamente, da
  profissional responsável pelo desenvolvimento do tratamento clínico, todas as informações sobre o
  tratamento/procedimento ao qual será submetido(a) e que elas foram de sua inteira compreensão.
</p>
<p class="paragrafo"><strong>PARÁGRAFO SEGUNDO:</strong> A CONTRATANTE declara ter sido orientada pessoalmente de
  todos os cuidados pré e pós sessões clínicas que deverão ser seguidos, bem como sobre as complicações e
  interferências que podem acontecer no tratamento optado.
</p>
<p class="paragrafo"><strong>PARÁGRAFO TERCEIRO:</strong> A CONTRATANTE declara estar ciente de que o resultado do
  tratamento estético não depende somente do trabalho da profissional responsável pelo desenvolvimento do
  procedimento, mas também de seus cuidados pessoais, sobretudo das reações imprevisíveis do seu organismo.
</p>

<p class="clausula"><strong>CLÁUSULA 11ª – ALTERAÇÕES:</strong> Este contrato não poderá ser alterado, modificado
  ou complementado sem que para isto haja acordo por escrito entre as partes.
</p>

<p class="clausula"><strong>CLÁUSULA 12ª – FORO:</strong> Fica eleito o foro da Comarca de Londrina/PR para
  dirimir quaisquer dúvidas ou controvérsias relativas a este instrumento.
</p>

<?php if (!empty($contract->notes)): ?>
  <p class="clausula"><strong>OBSERVAÇÕES ADICIONAIS:</strong></p>
  <p><?= nl2br(htmlspecialchars($contract->notes)) ?></p>
<?php endif; ?>

<p>
  E, por estarem justas e contratadas, tendo pleno conhecimento de seu conteúdo e alcance legal, as partes assinam
  o presente instrumento em 02 (duas) vias de igual teor e forma, conjuntamente com 02 (duas) testemunhas.
</p>

<p class="centro" style="margin-top:24px;">Londrina, <?= $dataExtenso ?>.</p>

<div class="assinaturas">
  <div class="linha-assinatura"></div>
  <p class="centro"><strong>CONTRATANTE</strong> — <?= htmlspecialchars($contract->patientName ?? '') ?></p>

  <div class="linha-assinatura"></div>
  <p class="centro"><strong>NATÁLIA BALBINOTTI ESTÉTICA INTEGRATIVA LTDA</strong><br>CONTRATADA</p>

  <div class="linha-assinatura"></div>
  <p class="centro">Testemunha 1 — Nome completo / RG/CPF</p>

  <div class="linha-assinatura"></div>
  <p class="centro">Testemunha 2 — Nome completo / RG/CPF</p>
</div>

</body>
</html>
