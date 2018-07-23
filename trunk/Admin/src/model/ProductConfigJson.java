package model;

import java.awt.Graphics2D;
import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.lang.reflect.Type;
import java.util.ArrayList;

import javax.imageio.ImageIO;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.JsonArray;
import com.google.gson.JsonDeserializationContext;
import com.google.gson.JsonDeserializer;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParseException;
import com.google.gson.JsonPrimitive;
import com.google.gson.reflect.TypeToken;
import com.vaadin.server.StreamResource;

public class ProductConfigJson {

	public static class Color {
		public String name;
		public String value;
		
		
		private static class ColorImage implements StreamResource.StreamSource {

			private String color;
			ByteArrayOutputStream imagebuffer = null;
			
			public ColorImage(String color) {
				this.color = color;
			}
			
			@Override
			public InputStream getStream() {
				BufferedImage image = new BufferedImage(50, 50, BufferedImage.TYPE_INT_ARGB);
				Graphics2D graphics = image.createGraphics();
				graphics.setPaint(java.awt.Color.decode(color));
				graphics.fillRect(0, 0, image.getWidth(), image.getHeight());
				try {
		            imagebuffer = new ByteArrayOutputStream();
		            ImageIO.write(image, "png", imagebuffer);
		            
		            return new ByteArrayInputStream(imagebuffer.toByteArray());
		        } catch (IOException e) {
		            return null;
		        }
			}
			
		}
		
		public String getName() {
			return this.name;
		}
		
		public void setName(String name) {
			this.name = name;
		}
		
		public String getValue() {
			return this.value;
		}
		
		public void setValue(String value) {
			this.value = value;
		}
		
		public StreamResource getIcon() {
			return new StreamResource(new ColorImage(getValue()), getName());
		}
		
		public String getCaption() {
			return getName() + ": " + getValue();
		}
		
		public Color(String name, String value) {
			this.name = name;
			this.value = value;
		}
	}
	
	public static class Overlay {
		public String image;
		public float x1;
		public float y1;
		public float x2;
		public float y2;
		
		public Overlay(String image, float x1, float y1, float x2, float y2) {
			this.image = image;
			this.x1 = x1;
			this.x2 = x2;
			this.y1 = y1;
			this.y2 = y2;
		}
	}
	
	private static class ProductConfigJsonDeserializer implements JsonDeserializer<ProductConfigJson> {

		@Override
		public ProductConfigJson deserialize(JsonElement json, Type typeOfT,
				JsonDeserializationContext context) throws JsonParseException {
			Gson gson = new Gson();
			ProductConfigJson configJson = new ProductConfigJson();
			JsonObject jsonobject = json.getAsJsonObject();
			JsonPrimitive weight = jsonobject.getAsJsonPrimitive("weight");
			JsonArray overlay = jsonobject.getAsJsonArray("overlay");
			JsonArray colors = jsonobject.getAsJsonArray("colors");
			if (weight != null) {
				configJson.weight = weight.getAsInt();
			}
			if (colors != null) {
				Type typeToken = new TypeToken<ArrayList<Color>>() { }.getType();
				configJson.colors = gson.fromJson(colors.toString(), typeToken);
			}
			if (overlay != null) {
				configJson.overlay = new Overlay(overlay.get(0).getAsString(), overlay.get(1).getAsFloat(),overlay.get(2).getAsFloat(),overlay.get(3).getAsFloat(),overlay.get(4).getAsFloat());
			}
			return configJson;
		}
		
	}
	
	public ArrayList<Color> colors;
	public Overlay overlay;
	public int weight = 0;
	
	public String getJson() {
		StringBuilder json = new StringBuilder();
		Gson gson = new Gson();
		json.append("{");
		json.append("\"weight\":\"");
		json.append(weight);
		json.append("\"");
		if (overlay != null) {
			json.append(",\"overlay\":[\"");
			json.append(overlay.image);
			json.append("\",");
			json.append(overlay.x1);
			json.append(",");
			json.append(overlay.y1);
			json.append(",");
			json.append(overlay.x2);
			json.append(",");
			json.append(overlay.y2);
			json.append("]");
		}
		if (colors != null) {
			json.append(",\"colors\":");
			json.append(gson.toJson(colors));
		}
		json.append("}");
		return json.toString();
	}
	
	
	//note, make deserialiser a static variable
	public static ProductConfigJson getProductConfig(String json) {
		Gson gson = new GsonBuilder().registerTypeAdapter(ProductConfigJson.class, new ProductConfigJsonDeserializer()).create();
		return gson.fromJson(json, ProductConfigJson.class);
	}

}
