package webServices;

import java.util.LinkedHashMap;
import java.util.List;


public class Order {
	int id;
	String createdOnUtc;
	Address billingAddress;
	Address shippingAddress;
	String paymentMethod;
	String shippingMethod;
	String discountCoupon;
	List<LinkedHashMap<String, String>> products;
	
	public Order()
	{
		
	}
	
	public Address getShippingAddress() {
		return shippingAddress;
	}

	public void setShippingAddress(Address shippingAddress) {
		this.shippingAddress = shippingAddress;
	}

	public Address getBillingAddress() {
		return billingAddress;
	}

	public void setBillingAddress(Address billingAddress) {
		this.billingAddress = billingAddress;
	}

	public int getId() {
		return id;
	}
	
	public void setId(int id) {
		this.id = id;
	}

	public String getCreatedOnUtc() {
		return createdOnUtc;
	}

	public void setCreatedOnUtc(String createdOnUtc) {
		this.createdOnUtc = createdOnUtc;
	}
	
	public String getPaymentMethod() {
		return paymentMethod;
	}

	public void setPaymentMethod(String paymentMethod) {
		this.paymentMethod = paymentMethod;
	}

	
	public String getShippingMethod() {
		return shippingMethod;
	}

	public void setShippingMethod(String shippingMethod) {
		this.shippingMethod = shippingMethod;
	}


	public String getDiscountCoupon() {
		return discountCoupon;
	}

	public void setDiscountCoupon(String discountCoupon) {
		this.discountCoupon = discountCoupon;
	}

	public List<LinkedHashMap<String, String>> getProducts(){
		return this.products;
	}
	
	public void setProducts(List<LinkedHashMap<String, String>> products){
		this.products = products;
	}


	public class Address
	{
		
		String Company;
		String FirstName;
		String LastName;
		String PhoneNumber;
		String Address1;
		String Address2;
		String City;
		String StateProvince;
		String Country;
		String ZipPostalCode;
		

		public String getCompany() {
			return Company;
		}

		public void setCompany(String company) {
			Company = company;
		}

		public String getFirstName() {
			return FirstName;
		}

		public void setFirstName(String firstName) {
			FirstName = firstName;
		}

		public String getLastName() {
			return LastName;
		}

		public void setLastName(String lastName) {
			LastName = lastName;
		}

		public String getPhoneNumber() {
			return PhoneNumber;
		}

		public void setPhoneNumber(String phone) {
			PhoneNumber = phone;
		}

		public String getAddress1() {
			return Address1;
		}

		public void setAddress1(String address1) {
			Address1 = address1;
		}

		public String getAddress2() {
			return Address2;
		}

		public void setAddress2(String address2) {
			Address2 = address2;
		}

		public String getCity() {
			return City;
		}

		public void setCity(String city) {
			City = city;
		}

		public String getStateProvince() {
			return StateProvince;
		}

		public void setStateProvince(String stateProvince) {
			StateProvince = stateProvince;
		}

		public String getCountry() {
			return Country;
		}

		public void setCountry(String country) {
			Country = country;
		}

		public String getZipPostalCode() {
			return ZipPostalCode;
		}

		public void setZipPostalCode(String zipPostalCode) {
			ZipPostalCode = zipPostalCode;
		}
		
	}
	
	public class Products
	{
		String Name;
		String imageUrl;
		String CategoryName;
		String ManufacturerPartNumber;
		String Quantity;
		String QuantityShipped;
		
		public String getName() {
			return Name;
		}
		
		public void setName(String name) {
			Name = name;
		}
		
		public String getImageUrl() {
			return imageUrl;
		}
		
		public void setImageUrl(String imageUrl) {
			this.imageUrl = imageUrl;
		}

		public String getCategoryName() {
			return CategoryName;
		}

		public void setCategoryName(String categoryName) {
			CategoryName = categoryName;
		}

		public String getManufacturerPartNumber() {
			return ManufacturerPartNumber;
		}

		public void setManufacturerPartNumber(String manufacturerPartNumber) {
			ManufacturerPartNumber = manufacturerPartNumber;
		}

		public String getQuantity() {
			return Quantity;
		}

		public void setQuantity(String quantity) {
			Quantity = quantity;
		}

		public String getQuantityShipped() {
			return QuantityShipped;
		}

		public void setQuantityShipped(String quantityShipped) {
			QuantityShipped = quantityShipped;
		}
		
	}
}
