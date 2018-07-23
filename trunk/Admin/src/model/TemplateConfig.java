package model;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

public class TemplateConfig implements Serializable {

	public static class Misc {
		public String image_palette_1_rid;
		public List<Integer> template_set_1;
		public List<String> image_palette_1_colors;
		
		public Misc() {
			template_set_1 = null;
			image_palette_1_colors = null;
		}
	}
	
	public Misc misc;
	
	public TemplateConfig() {
		misc = new Misc();
	}
}
