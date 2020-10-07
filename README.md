Módulo para integração com o gatway de pagamentos IUGU

Instalação manual

Baixe os arquivos para dentro da pasta: app/code/Iugu/Payment

Execute os seguintes comandos na linha de comando:

```
 bin/magento setup:upgrade
 bin/magento setup:di:compile
 bin/magento cache:clean
 bin/magento setup:static-content:deploy
```

Configuração

- Entre no painel de controle da Iugu e copie a chave da sua conta e a chave da API (teste ou produção).
- No painel de controle do magento (admin), vá para Lojas->Configuração->Vendas->Métodos de pagamento->Iugu
- Cole as chaves da conta e da API.
- Recomendamos testar primeiro no ambiente de testes.
- Configure o Cartão de Crédito e o Boleto Bancário.

- No painel de controle da Iugu->Configurações->Desenvolvedor->Comunicação via Gatilhos->Novo 
- Em URL, cole: https://{{sua url}}/index.php/rest/V1/iugu-payment/invoice/event
- Em evento, selecione: Mudança de estado de Fatura.
