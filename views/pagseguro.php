<html>
<head>
    <title>PagSeguro Checkout Test</title>
</head>
<body>
<section class="container-fluid">
    <div class="row">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <form method="post" action="./home/checkout/">
                        <h2>Checkout</h2>
                        <hr>
                        <h4>
                            Carrinho
                        </h4>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table id="cart" class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Produto</th>
                                        <th scope="col">Preço unitário</th>
                                        <th scope="col">Quantidade</th>
                                        <th scope="col">Preço total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <h4>
                            Dados cadastrais
                        </h4>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="senderName">Nome completo</label>
                                    <input class="form-control" id="senderName" name="senderName" placeholder="Nome completo" value="Matheus Prado Rodrigues">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="senderEmail">Endereço de e-mail</label>
                                    <input class="form-control" id="senderEmail" name="senderEmail" placeholder="E-mail" value="matheus@sandbox.pagseguro.com.br">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="senderCPF">CPF</label>
                                    <input class="form-control" id="senderCPF" name="senderCPF" placeholder="E-mail" value="093.103.349-75">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="senderAreaCode">DDD</label>
                                    <input class="form-control" id="senderAreaCode" name="senderAreaCode" placeholder="DDD" value="43">
                                </div>
                            </div>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <label for="senderPhone">Telefone / Celular</label>
                                    <input class="form-control" id="senderPhone" name="senderPhone" placeholder="Telefone / Celular" value="999282976">
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <h4>
                                    Endereço
                                </h4>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="billingAddressPostalCode">CEP</label>
                                    <input class="form-control" id="billingAddressPostalCode" name="billingAddressPostalCode" placeholder="CEP" value="86.077-260">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="billingAddressStreet">Rua</label>
                                    <input class="form-control" id="billingAddressStreet" name="billingAddressStreet" placeholder="Rua" readonly>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="billingAddressNumber">Número</label>
                                    <input class="form-control" id="billingAddressNumber" name="billingAddressNumber" placeholder="Número" value="44">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="billingAddressState">Estado</label>
                                    <input class="form-control" id="billingAddressState" name="billingAddressState" placeholder="Estado" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="billingAddressCity">Cidade</label>
                                    <input class="form-control" id="billingAddressCity" name="billingAddressCity" placeholder="Cidade" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="billingAddressDistrict">Bairro</label>
                                    <input class="form-control" id="billingAddressDistrict" name="billingAddressDistrict" placeholder="Bairro" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="billingAddressComplement">Complemento</label>
                                    <input class="form-control" id="billingAddressComplement" name="billingAddressComplement" placeholder="Complemento" value="Casa">
                                </div>
                            </div>
                            <input type="hidden" id="billingAddressCountry" name="billingAddressCountry" value="BRA">

                            <div class="col-lg-12">
                                <hr>
                                <h4>
                                    Endereço de entrega
                                </h4>

                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="shippingAddressRequired"><input id="shippingAddressRequired" value="1" type="checkbox"><span></span><span>Os produtos deverão ser entregues no meu endereço?</span></label>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 none">
                                        <div class="form-group">
                                            <label for="useSameLocation"><input id="useSameLocation" value="1" type="checkbox"><span></span><span>Usar o local onde moro como endereço de entrega?</span></label>
                                        </div>
                                    </div>

                                    <div id="sameLocation" class="col-lg-12 none">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="shippingAddressPostalCode">CEP</label>
                                                    <input class="form-control" id="shippingAddressPostalCode" name="shippingAddressPostalCode" placeholder="CEP">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="shippingAddressStreet">Rua</label>
                                                    <input class="form-control" id="shippingAddressStreet" name="shippingAddressStreet" placeholder="Rua" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="shippingAddressNumber">Número</label>
                                                    <input class="form-control" id="shippingAddressNumber" name="shippingAddressNumber" placeholder="Número">
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="shippingAddressState">Estado</label>
                                                    <input class="form-control" id="shippingAddressState" name="shippingAddressState" placeholder="Estado" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="shippingAddressCity">Cidade</label>
                                                    <input class="form-control" id="shippingAddressCity" name="shippingAddressCity" placeholder="Cidade" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="shippingAddressDistrict">Bairro</label>
                                                    <input class="form-control" id="shippingAddressDistrict" name="shippingAddressDistrict" placeholder="Bairro" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="shippingAddressComplement">Complemento</label>
                                                    <input class="form-control" id="shippingAddressComplement" name="shippingAddressComplement" placeholder="Complemento">
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label>Tipo de frete</label>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="shippingPAC"><input id="shippingPAC" type="radio" name="shippingType" value="1" checked/><span></span><span>PAC</span></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="shippingSEDEX"><input id="shippingSEDEX" type="radio" name="shippingType" value="1"/><span></span><span>SEDEX</span></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="shippingCost">Valor do frete</label>
                                                    <input class="form-control" id="shippingCost" name="shippingCost" placeholder="Valor do frete" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label class="label-primary" id="remainingDays">&nbsp;&nbsp;&nbsp;</label>
                                                    <button style="width: 100%" id="calcFrete" type="button" class="btn btn-primary">Calcular frete</button>
                                                </div>
                                            </div>
                                            <input type="hidden" id="shippingAddressCountry" name="shippingAddressCountry" value="BRA">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="row none">
                            <div class="col-lg-12">
                                <hr>
                                <h4>
                                    Método de pagamento
                                </h4>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div id="methods" class="row">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div data-active="credit_card" class="row none">
                            <div class="col-lg-12">
                                <hr>
                                <h4>Dados do cartão</h4>
                                <h5>Bandeiras aceitas para este checkout</h5>
                                <ul id="brands"></ul>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="creditCardNumber">Número do cartão</label>
                                    <input class="form-control" id="creditCardNumber" name="creditCardNumber" placeholder="Número do cartão" value="5234.2189.0555.5590">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="creditCardHolderName">Nome impresso no cartão</label>
                                    <input class="form-control" id="creditCardHolderName" name="creditCardHolderName" placeholder="Nome impresso no cartão" value="Matheus P Rodrigues">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="creditCardCVV">Código de segurança</label>
                                    <input class="form-control" id="creditCardCVV" name="creditCardCVV" placeholder="Código de segurança" value="936">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="creditCardValid">Válido até</label>
                                    <input class="form-control" id="creditCardValid" name="creditCardValid" placeholder="Válido até" value="07/2020">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="creditCardHolderCPF">CPF</label>
                                    <input class="form-control" id="creditCardHolderCPF" name="creditCardHolderCPF" placeholder="CPF" value="093.103.349.75">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="creditCardHolderAreaCode">DDD</label>
                                    <input class="form-control" id="creditCardHolderAreaCode" name="creditCardHolderAreaCode" placeholder="DDD" value="43">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="creditCardHolderPhone">Telefone ou celular</label>
                                    <input class="form-control" id="creditCardHolderPhone" name="creditCardHolderPhone" placeholder="Telefone / celular" value="999282976">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="creditCardHolderBirthDate">Data de nascimento</label>
                                    <input class="form-control" id="creditCardHolderBirthDate" name="creditCardHolderBirthDate" placeholder="Data de nascimento" value="15/09/1994">
                                </div>
                            </div>
                            <div class="col-lg-6 none">
                                <div class="form-group">
                                    <label for="installmentValue">Forma de pagamento</label>
                                    <select class="form-control" id="installmentValue" name="installmentValue">

                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 none">
                                <div class="form-group">
                                    <label for="installmentQuantity">Forma de pagamento</label>
                                    <select class="form-control" id="installmentQuantity" name="installmentQuantity">

                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <button style="width: 100%" type="submit" class="btn btn-success">Finalizar</button>
                                </div>
                            </div>
                            <input type="hidden" id="creditCardToken" name="creditCardToken">
                            <input type="hidden" id="noInterestInstallmentQuantity" name="noInterestInstallmentQuantity">
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>


<?php

