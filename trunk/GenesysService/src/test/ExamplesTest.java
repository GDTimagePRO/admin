package test;

import static org.junit.Assert.*;

import java.io.IOException;

import org.junit.Test;

import backend.DbOrderExamples.ConfigItem;

import com.google.gson.Gson;

import examples.SettingsExamples;
import examples.SettingsExamples.Result;

public class ExamplesTest {
	
	//Test should get an error code of 1 since the id 1 should not exist
	@Test
	public void resourceOpCopyBasicTest() throws IOException {
		
	   Result result = SettingsExamples.resourceOpCopy(1, 1);
	   assertEquals("Correctly parse json", 1, result.errorCode);
	 }
	
	
	//Basic kind of unfinished test
	@Test
	public void configItemToFromJsonTest() {
		ConfigItem configItem = new ConfigItem();
		configItem.productId = 1;
		configItem.colors = 1;
		configItem.misc = 1;
		configItem.templateCategoryId = 1;
		configItem.templateId = 1;
		
		String output = configItem.toJSON();
		ConfigItem configItem2 = ConfigItem.fromJSON(output);
		
		assertEquals("Correctly convert to json and then from", configItem2.productId, configItem.productId);
	}
	
}
