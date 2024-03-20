<?php

namespace App;
use JsonSerializable;

class Product implements JsonSerializable
{
    private bool $isAvailable;
    
    private int $capacity;
    
    private float $price;
    
    private string $title;
    
    private string $imageUrl;
    
    private string $color;
    
    private string $availabilityText;
    
    private ?string $shippingText;
    
    private ?string $shippingDate;

    /**
     * jsonSerialize
     *
     * @return void
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
    
    /**
     * setTitle
     *
     * @param  mixed $title
     * @return void
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }
    
    /**
     * setPrice
     *
     * @param  mixed $price
     * @return void
     */
    public function setPrice(float $price) {
        $this->price = $price;
    }
    
    /**
     * setImageUrl
     *
     * @param  mixed $imageUrl
     * @return void
     */
    public function setImageUrl(string $imageUrl) {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * setCapacity
     *
     * @param  mixed $capacity
     * @return void
     */
    public function setCapacity(int $capacity) {
        $this->capacity = $capacity;
    }
    
    /**
     * setColor
     *
     * @param  mixed $color
     * @return void
     */
    public function setColor(string $color) {
        $this->color = $color;
    }
    
    /**
     * setAvailabilityText
     *
     * @param  mixed $availabilityText
     * @return void
     */
    public function setAvailabilityText(string $availabilityText) {
        $this->availabilityText = $availabilityText;
    }
    
    /**
     * setIsAvailable
     *
     * @param  mixed $isAvailable
     * @return void
     */
    public function setIsAvailable(bool $isAvailable) {
        $this->isAvailable = $isAvailable;
    }
    
    /**
     * setShippingText
     *
     * @param  mixed $shippingText
     * @return void
     */
    public function setShippingText(?string $shippingText) {
        $this->shippingText = $shippingText;
    }
    
    /**
     * setShippingDate
     *
     * @param  mixed $shippingDate
     * @return void
     */
    public function setShippingDate(?string $shippingDate) {
        $this->shippingDate = $shippingDate;
    }
}
