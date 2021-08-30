<?php

//traemos la lib de conekta





class Payment{
    //Ky priv
    private $ApiKey = "key_rsCvg5eQGB3HMqPFa5QrAQ";
    private $ApiVersion = "2.0.0";

    public function __construct($token, $card, $name, $description,$total,$email){
        $this->token=$token;
        $this->card=$card;
        $this->name=$name;
        $this->description = $description;
        $this->total = $total;
        $this->email = $email;
    }


    public function Pay(){
        \Conekta\Conekta::setApiKey($this->ApiKey);
        \Conekta\Conekta::setApiVersion($this->ApiVersion);

        if(!$this->Validate()){
            return false;
        }
        if(!$this->CreateCustomer()){
            return false;
        }
        if(!$this->CreateOrder()){
            return false;
        }
        return true;
            
    }
    public function Validate(){
        if($this->card=="" || $this->name =="" || $this->description=="" || $this->total == "" || $this->email==""){
            $this->error="El número de tarjeta, el nombre, concepto, monto y correo electrónico son obligatorios";
            return false;
        }
        if(strlen($this->card)<=14){
            $this->error="El número de tarjeta debe tener al menos 15 caracteres";
            return false;
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            $this->error="El correo electrónico no tiene un formato valido";
            return false;
        }
        if($this->total<=20){
            $this->error="El monto debe ser mayor a 20 pesos";
            return false;
        }
        return true;
    }

    public function CreateCustomer(){
        try {

            $this->customer = \Conekta\Customer::create(
                array(
                    "name" => $this->name,
                    "email" => $this->email,
                    "payment_sources" => array(
                        array(
                            "type" => "card",
                            "token_id" => $this->token
                        )
                    ) //Fuentes de Pago
                )//Cliente
            );

        } catch (\Conekta\ProcessingError $error) {
            $this->error = $error->getMessage();
            return false;
        } catch (\Conekta\Handler $error){
            $this->error = $error->getMessage();
            return false;
        } catch (\Conekta\ParameterValidationError $error){
            $this->error = $error->getMessage();
            return false;
        }

        return true;
    }
    public function CreateOrder(){
        try{
   
            $this->order = \Conekta\Order::create(
                array(
                    "amount"=>$this->total,
                    "line_items" => array(
                        array(
                            "name" => $this->description,
                            "unit_price" => $this->total*100, //se multiplica por 100 así lo pide conekta
                            "quantity" => 1
                        )//first line_item
                    ), //line_items
                    "currency" => "MXN",
                    "customer_info" => $this->customer_info, //customer_info
                    "charges" => $this->charge_array //charges
                )//order
            );
        } catch (\Conekta\ProcessingError $error){
            $this->error=$error->getMessage();
            return false;
        } catch (\Conekta\ParameterValidationError $error){
            $this->error=$error->getMessage();
            return false;
        } catch (\Conekta\Handler $error){
            $this->error=$error->getMessage();
            return false;
        }



        return true;
    }
}
?>