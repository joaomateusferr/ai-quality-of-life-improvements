# Extração de Holerite para JSON

## Objetivo

Analise o conteúdo textual de um holerite e extraia todas as verbas (proventos e descontos), retornando um único JSON válido.

## Regras Gerais

- Não faça suposições.
- Utilize apenas informações presentes no texto fornecido.
- Não invente valores, códigos ou descrições.
- Caso um campo não possa ser identificado com confiança, retorne `null`.
- Se existirem registros exatamente iguais (mesmo código, descrição, referência, vencimento e desconto), retorne apenas um deles.
- Converta todos os valores monetários para número decimal utilizando ponto (`.`) como separador decimal.
  - Exemplo: `1250.99`
- Não utilize separador de milhares.
- O campo `referencia` deve ser retornado exatamente como aparece no documento. Caso não exista, retorne `null`.
- Retorne apenas um JSON válido em uma única linha.
- Não utilize Markdown, comentários ou qualquer texto adicional.

## Identificação do período

Identifique o mês de referência do holerite.

Retorne o campo:

- `mes`: mês de competência no formato `YYYY-MM`, quando possível.
- Caso não seja possível identificar com segurança, retorne `null`.

## Tipo do documento

Determine se o documento representa:

- pagamento
- adiantamento

Retorne o campo:

- `tipo`: `"pagamento"` ou `"adiantamento"`.

Caso não seja possível identificar com segurança, retorne `null`.

## Campos de cada verba

Cada item deve conter os seguintes campos:

| Campo | Tipo |
|--------|------|
| codigo | number \| null |
| descricao | string |
| referencia | string \| null |
| vencimento | number \| null |
| desconto | number \| null |

## Estrutura esperada

```json
{
  "mes": "2025-06",
  "tipo": "pagamento",
  "itens": [
    {
      "codigo": "001",
      "descricao": "SALARIO BASE",
      "referencia": "220:00",
      "vencimento": 3500.00,
      "desconto": null
    },
    {
      "codigo": "550",
      "descricao": "INSS",
      "referencia": null,
      "vencimento": null,
      "desconto": 412.35
    }
  ]
}
```

## Regras de extração

1. Extraia todas as verbas existentes.
2. Preserve a descrição exatamente como aparece no documento.
3. Preserve o código exatamente como aparece no documento.
4. Preserve a referência exatamente como aparece no documento.
5. Um item pode possuir apenas vencimento, apenas desconto ou ambos, caso isso esteja explicitamente informado.
6. Não crie itens inexistentes.
7. Não consolide verbas diferentes.
8. Elimine apenas registros completamente duplicados (todos os campos iguais).
9. Ignore linhas de totais, subtotais, bases de cálculo, informações cadastrais, dados bancários, mensagens, observações e demais informações que não representem verbas do holerite.
10. Considere apenas linhas que representem efetivamente um lançamento de folha de pagamento.
11. Caso algum campo de um lançamento não possa ser identificado com confiança, utilize `null` apenas nesse campo.
12. O JSON retornado deve ser sintaticamente válido e conter apenas os campos definidos acima.