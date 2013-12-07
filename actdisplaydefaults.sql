// Search and replace:
// Rhinestone Review -> Show Id for Rhinestone Review
// Main Event --> Show Id
// Bordello --> Show Id
// Sunday --> Show Id
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, '', 'Performer Information', '', 'none', 1, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'MobilePhone', 'Mobile phone', '', 'textbox', 3, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Email', 'Email', '', 'textbox', 4, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Hotel', 'Staying at Hotel', 'You can make your discounted reservation by visiting <a href=https://resweb.passkey.com/go/burlesque2013>this webpage</a> or calling (888) 421-1442', 'radio', 5, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, '', 'Audio Info', '', 'none', 6, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Song', 'Name of Song', '', 'textbox', 7, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Artist', 'Name of Artist', '', 'textbox', 8, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Song', 'Length of Song', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 9, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Act', 'Length of Act', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 10, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'MusicPath', 'Attach Your Music in .mp3 format', 'Music should be under 40MB in size', 'file', 11, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'SoundInstruct', 'Instructions to Sound Tech', 'For example:  Start music when I stand on the chair, Start music after MC leaves stage, etc.', 'textarea', 12, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'HaveMusic', 'I don''t perform with music', '', 'checkbox', 13, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'NeedMic', 'I will need a microphone', '', 'checkbox', 14, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, '', 'Lighting Info', '', 'none', 15, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'LightingInstruct', 'Instructions for the Lighting Tech', 'Examples: This is a sad piece, use lots of blues; Start with just a follow spot,  at the cymbal crash at 1 minute and 14 seconds, go really bright on stage; I start in the audience and move to the stage, etc.\r\n\r\nIf you have lighting cues, be specific and repetitive.  For example<p>\r\n- Start with just a spotlight\r\n\r\n- At 37 seconds (during the big trumpet solo) switch to a lot of red lights on stage.\r\n\r\nSee? That''s both an audio cue and a timing cue.', 'textarea', 16, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'StageColor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 17, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'StageSecondColor', 'Secondary Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 18, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'CycColor', 'Color for Cyc Lighting', 'This is the primary color used on the curtains at the back of the stage', 'radio', 19, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'StageColorVendor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 20, 0, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'FollowSpot', 'Would You Like a Follow-spot?', '', 'checkbox', 21, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Backlight', 'Would You Like a Backlight?', '', 'checkbox', 22, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, '', 'Props', '', 'none', 23, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'Props', 'No props or set pieces', '', 'checkbox', 24, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'SetProps', 'I have props I need set before my number', '', 'checkbox', 25, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'ClearProps', 'I carry my props on with me, but will need them cleared when I''m done.', '', 'checkbox', 26, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'CueProps', 'I will need a stage kitten to hand me a prop on cue during my act (You must be at tech rehearsal if you pick this option) ', '', 'checkbox', 27, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'PropInstruct', 'Tell Us About Your Prop/Set Needs', '', 'textarea', 28, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, RhinestoneReview, 'IntroText', 'Intro Text', 'Please write a brief introduction.  Your introduction will be used by the M.C., but probably not read word for word.', 'textarea', 29, 1, 0);


INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, '', 'Performer Information', '', 'none', 1, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'MobilePhone', 'Mobile phone', '', 'textbox', 3, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Email', 'Email', '', 'textbox', 4, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Hotel', 'Staying at Hotel', 'You can make your discounted reservation by visiting <a href=https://resweb.passkey.com/go/burlesque2013>this webpage</a> or calling (888) 421-1442', 'radio', 5, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, '', 'Audio Info', '', 'none', 6, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Song', 'Name of Song', '', 'textbox', 7, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Artist', 'Name of Artist', '', 'textbox', 8, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Song', 'Length of Song', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 9, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Act', 'Length of Act', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 10, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'MusicPath', 'Attach Your Music in .mp3 format', 'Music should be under 40MB in size', 'file', 11, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'SoundInstruct', 'Instructions to Sound Tech', 'For example:  Start music when I stand on the chair, Start music after MC leaves stage, etc.', 'textarea', 12, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'HaveMusic', 'I don''t perform with music', '', 'checkbox', 13, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'NeedMic', 'I will need a microphone', '', 'checkbox', 14, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, '', 'Lighting Info', '', 'none', 15, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'LightingInstruct', 'Instructions for the Lighting Tech', 'Examples: This is a sad piece, use lots of blues; Start with just a follow spot,  at the cymbal crash at 1 minute and 14 seconds, go really bright on stage; I start in the audience and move to the stage, etc.\r\n\r\nIf you have lighting cues, be specific and repetitive.  For example<p>\r\n- Start with just a spotlight\r\n\r\n- At 37 seconds (during the big trumpet solo) switch to a lot of red lights on stage.\r\n\r\nSee? That''s both an audio cue and a timing cue.', 'textarea', 16, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'StageColor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 17, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'StageSecondColor', 'Secondary Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 18, 0, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'CycColor', 'Color for Cyc Lighting', 'This is the primary color used on the curtains at the back of the stage', 'radio', 19, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'StageColorVendor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 20, 0, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'FollowSpot', 'Would You Like a Follow-spot?', '', 'checkbox', 21, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Backlight', 'Would You Like a Backlight?', '', 'checkbox', 22, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, '', 'Props', '', 'none', 23, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'Props', 'No props or set pieces', '', 'checkbox', 24, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'SetProps', 'I have props I need set before my number', '', 'checkbox', 25, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'ClearProps', 'I carry my props on with me, but will need them cleared when I''m done.', '', 'checkbox', 26, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'CueProps', 'I will need a stage kitten to hand me a prop on cue during my act (You must be at tech rehearsal if you pick this option) ', '', 'checkbox', 27, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'PropInstruct', 'Tell Us About Your Prop/Set Needs', '', 'textarea', 28, 0, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, MainEvent, 'IntroText', 'Intro Text', 'Please write a brief introduction.  Your introduction will be used by the M.C., but probably not read word for word.', 'textarea', 29, 1, 0);

INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, '', 'Performer Information', '', 'none', 1, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'MobilePhone', 'Mobile phone', '', 'textbox', 3, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Email', 'Email', '', 'textbox', 4, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Hotel', 'Staying at Hotel', 'You can make your discounted reservation by visiting <a href=https://resweb.passkey.com/go/burlesque2013>this webpage</a> or calling (888) 421-1442', 'radio', 5, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, '', 'Audio Info', '', 'none', 6, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Song', 'Name of Song', '', 'textbox', 7, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Artist', 'Name of Artist', '', 'textbox', 8, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Song', 'Length of Song', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 9, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Act', 'Length of Act', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 10, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'MusicPath', 'Attach Your Music in .mp3 format', 'Music should be under 40MB in size', 'file', 11, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'SoundInstruct', 'Instructions to Sound Tech', 'For example:  Start music when I stand on the chair, Start music after MC leaves stage, etc.', 'textarea', 12, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'HaveMusic', 'I don''t perform with music', '', 'checkbox', 13, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'NeedMic', 'I will need a microphone', '', 'checkbox', 14, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, '', 'Lighting Info', '', 'none', 15, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'LightingInstruct', 'Instructions for the Lighting Tech', 'Examples: This is a sad piece, use lots of blues; Start with just a follow spot,  at the cymbal crash at 1 minute and 14 seconds, go really bright on stage; I start in the audience and move to the stage, etc.\r\n\r\nIf you have lighting cues, be specific and repetitive.  For example<p>\r\n- Start with just a spotlight\r\n\r\n- At 37 seconds (during the big trumpet solo) switch to a lot of red lights on stage.\r\n\r\nSee? That''s both an audio cue and a timing cue.', 'textarea', 16, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'StageColor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 17, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'StageSecondColor', 'Secondary Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 18, 0, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'CycColor', 'Color for Cyc Lighting', 'This is the primary color used on the curtains at the back of the stage', 'radio', 19, 0, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'StageColorVendor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 20, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'FollowSpot', 'Would You Like a Follow-spot?', '', 'checkbox', 21, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Backlight', 'Would You Like a Backlight?', '', 'checkbox', 22, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, '', 'Props', '', 'none', 23, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'Props', 'No props or set pieces', '', 'checkbox', 24, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'SetProps', 'I have props I need set before my number', '', 'checkbox', 25, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'ClearProps', 'I carry my props on with me, but will need them cleared when I''m done.', '', 'checkbox', 26, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'CueProps', 'I will need a stage kitten to hand me a prop on cue during my act (You must be at tech rehearsal if you pick this option) ', '', 'checkbox', 27, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'PropInstruct', 'Tell Us About Your Prop/Set Needs', '', 'textarea', 28, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Bordello, 'IntroText', 'Intro Text', 'Please write a brief introduction.  Your introduction will be used by the M.C., but probably not read word for word.', 'textarea', 29, 1, 0);

INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, '', 'Performer Information', '', 'none', 1, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'MobilePhone', 'Mobile phone', '', 'textbox', 3, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Email', 'Email', '', 'textbox', 4, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Hotel', 'Staying at Hotel', 'You can make your discounted reservation by visiting <a href=https://resweb.passkey.com/go/burlesque2013>this webpage</a> or calling (888) 421-1442', 'radio', 5, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, '', 'Audio Info', '', 'none', 6, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Song', 'Name of Song', '', 'textbox', 7, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Artist', 'Name of Artist', '', 'textbox', 8, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Song', 'Length of Song', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 9, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Act', 'Length of Act', 'Your act can be no longer than 4 minutes and 30 seconds in total length.  If you have complicated props to set or clear, please make sure your music does not use the full length of your act.', 'time', 10, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'MusicPath', 'Attach Your Music in .mp3 format', 'Music should be under 40MB in size', 'file', 11, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'SoundInstruct', 'Instructions to Sound Tech', 'For example:  Start music when I stand on the chair, Start music after MC leaves stage, etc.', 'textarea', 12, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'HaveMusic', 'I don''t perform with music', '', 'checkbox', 13, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'NeedMic', 'I will need a microphone', '', 'checkbox', 14, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, '', 'Lighting Info', '', 'none', 15, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'LightingInstruct', 'Instructions for the Lighting Tech', 'Examples: This is a sad piece, use lots of blues; Start with just a follow spot,  at the cymbal crash at 1 minute and 14 seconds, go really bright on stage; I start in the audience and move to the stage, etc.\r\n\r\nIf you have lighting cues, be specific and repetitive.  For example<p>\r\n- Start with just a spotlight\r\n\r\n- At 37 seconds (during the big trumpet solo) switch to a lot of red lights on stage.\r\n\r\nSee? That''s both an audio cue and a timing cue.', 'textarea', 16, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'StageColor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 17, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'StageSecondColor', 'Secondary Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 18, 0, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'CycColor', 'Color for Cyc Lighting', 'This is the primary color used on the curtains at the back of the stage', 'radio', 19, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'StageColorVendor', 'Color for Stage Lighting', 'This is the primary color used on the main part of the stage', 'radio', 20, 0, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'FollowSpot', 'Would You Like a Follow-spot?', '', 'checkbox', 21, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Backlight', 'Would You Like a Backlight?', '', 'checkbox', 22, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, '', 'Props', '', 'none', 23, 1, 1);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'Props', 'No props or set pieces', '', 'checkbox', 24, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'SetProps', 'I have props I need set before my number', '', 'checkbox', 25, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'ClearProps', 'I carry my props on with me, but will need them cleared when I''m done.', '', 'checkbox', 26, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'CueProps', 'I will need a stage kitten to hand me a prop on cue during my act (You must be at tech rehearsal if you pick this option) ', '', 'checkbox', 27, 1, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'PropInstruct', 'Tell Us About Your Prop/Set Needs', '', 'textarea', 28, 0, 0);
INSERT INTO `ActTechDisplay` VALUES(NULL, Sunday, 'IntroText', 'Intro Text', 'Please write a brief introduction.  Your introduction will be used by the M.C., but probably not read word for word.', 'textarea', 29, 1, 0);




