<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'वीडियो ट्रैक';
$string['modulename'] = 'वीडियो ट्रैक';
$string['modulenameplural'] = 'वीडियो ट्रैक गतिविधियाँ';
$string['pluginadministration'] = 'वीडियो ट्रैक प्रशासन';
$string['videotrack:addinstance'] = 'नई वीडियो ट्रैक गतिविधि जोड़ें';
$string['videotrack:view'] = 'वीडियो ट्रैक देखें';
$string['videotrack:viewreport'] = 'वीडियो ट्रैक रिपोर्ट देखें';
$string['videotrack:viewownreport'] = 'अपनी वीडियो ट्रैक रिपोर्ट देखें';
$string['videoname'] = 'गतिविधि का नाम';
$string['youtubeurl'] = 'YouTube URL';
$string['youtubeurl_help'] = 'सामान्य YouTube watch URL, short URL या embed URL चिपकाएँ।';
$string['showcontrols'] = 'प्लेयर नियंत्रण दिखाएँ';
$string['disablekeyboard'] = 'कीबोर्ड शॉर्टकट अक्षम करें';
$string['showfullscreen'] = 'पूर्ण स्क्रीन बटन दिखाएँ';
$string['allowseekforward'] = 'आगे बढ़ने की अनुमति दें';
$string['allowseekbackward'] = 'पीछे जाने की अनुमति दें';
$string['allowplaybackratechange'] = 'प्लेबैक गति बदलने की अनुमति दें';
$string['countbyvideotime'] = 'वीडियो टाइमलाइन के आधार पर कवरेज गिनें';
$string['countbyvideotime_help'] = 'अनुशंसित। पूर्णता वीडियो टाइमलाइन पर अद्वितीय कवर किए गए सेकंड पर आधारित होती है, बार-बार देखने पर नहीं।';
$string['err:completionpercentrange'] = 'पूर्णता प्रतिशत 0 और 100 के बीच होना चाहिए।';
$string['completionpercent'] = 'आवश्यक पूर्णता प्रतिशत';
$string['completiondetail:percent'] = 'वीडियो का कम से कम {$a}% देखना आवश्यक है';
$string['completiondetail:minreactions'] = 'कम से कम {$a} भिन्न प्रतिक्रियाएँ आवश्यक हैं';
$string['completiondetail:allreactiontypes'] = 'प्रत्येक कॉन्फ़िगर की गई प्रतिक्रिया प्रकार के लिए कम से कम एक प्रतिक्रिया आवश्यक है';
$string['reactionsheader'] = 'प्रतिक्रियाएँ';
$string['reactionsenabled'] = 'प्रतिक्रियाएँ सक्षम करें';
$string['reactionsrequired'] = 'प्रतिक्रियाएँ अनिवार्य करें';
$string['minreactions'] = 'भिन्न प्रतिक्रियाओं की न्यूनतम संख्या';
$string['requireallreactiontypes'] = 'प्रत्येक कॉन्फ़िगर किए गए प्रकार के लिए कम से कम एक प्रतिक्रिया आवश्यक करें';
$string['completionlogic'] = 'पूर्णता तर्क';
$string['logicand'] = 'सभी सक्षम शर्तें (AND)';
$string['logicor'] = 'कोई भी सक्षम शर्त (OR)';
$string['clusterwindow'] = 'क्लस्टर विंडो (सेकंड)';
$string['showstudentreport'] = 'विद्यार्थियों को रिपोर्ट दिखाएँ';
$string['showreactionnotice'] = 'प्रतिक्रिया सूचना दिखाएँ';
$string['reactionnotice'] = 'प्रतिक्रिया सूचना';
$string['reactionlabel'] = 'प्रतिक्रिया लेबल';
$string['reactiondescription'] = 'प्रतिक्रिया विवरण';
$string['reactionicontype'] = 'आइकन प्रकार';
$string['reactioniconvalue'] = 'आइकन मान';
$string['reactioniconvalue_help'] = 'Emoji के लिए, emoji वर्ण दर्ज करें। Font Awesome के लिए, Moodle थीम द्वारा समर्थित class दर्ज करें, जैसे Font Awesome 5 थीम के लिए fa fa-smile या Font Awesome 6 थीम के लिए fa-regular fa-face-smile। आइकन उपलब्धता सक्रिय Moodle थीम और स्थापित Font Awesome संस्करण पर निर्भर करती है। अपलोड की गई आइकन फ़ाइल का उपयोग करते समय इस फ़ील्ड को खाली छोड़ें।';
$string['reactioniconfile'] = 'प्रतिक्रिया आइकन फ़ाइल';
$string['reactioniconfile_help'] = 'वैकल्पिक छवि फ़ाइल, जिसका उपयोग तब किया जाता है जब आइकन प्रकार “अपलोड की गई फ़ाइल” हो। स्वीकृत प्रारूप Moodle के वेब इमेज समर्थन पर निर्भर करते हैं।';
$string['reactionrequired'] = 'पूर्णता के लिए आवश्यक';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Font Awesome क्लास';
$string['icontype:file'] = 'अपलोड की गई फ़ाइल';
$string['addreaction'] = 'प्रतिक्रिया जोड़ें';
$string['invalidyoutubeurl'] = 'अमान्य YouTube URL।';
$string['err:minreactionsrequired'] = 'भिन्न प्रतिक्रियाओं की न्यूनतम संख्या सेट करें या सभी प्रतिक्रिया प्रकारों की आवश्यकता वाली शर्त सक्षम करें।';
$string['notice:minreactions'] = 'कम से कम {$a} भिन्न प्रतिक्रियाएँ आवश्यक हैं।';
$string['notice:requiredtypes'] = 'आवश्यक प्रतिक्रिया प्रकार: {$a}।';
$string['watch'] = 'देखें';
$string['reportstudent'] = 'मेरी प्रतिक्रियाएँ';
$string['reportteacher'] = 'शिक्षक रिपोर्ट';
$string['report:cumulative'] = 'संचयी';
$string['report:perstudent'] = 'प्रति विद्यार्थी';
$string['report:userid'] = 'उपयोगकर्ता';
$string['report:uniquecoveredseconds'] = 'अद्वितीय कवर किए गए सेकंड';
$string['report:completionpercent'] = 'पूर्णता %';
$string['report:lastposition'] = 'अंतिम स्थिति';
$string['report:iscompleted'] = 'पूर्ण';
$string['report:noattempts'] = 'कोई देखने का डेटा नहीं मिला।';
$string['report:noreactions'] = 'कोई प्रतिक्रिया डेटा नहीं मिला।';
$string['report:timestamp'] = 'टाइमस्टैम्प';
$string['report:reaction'] = 'प्रतिक्रिया';
$string['report:description'] = 'विवरण';
$string['report:clicks'] = 'क्लिक';
$string['report:students'] = 'विद्यार्थी';
$string['report:replay'] = 'खंड पुनः चलाएँ';
$string['report:delete'] = 'हटाएँ';
$string['report:sort'] = 'क्रमबद्ध करें';
$string['report:sorttime'] = 'टाइमस्टैम्प';
$string['report:sortreaction'] = 'प्रतिक्रिया';
$string['report:sortclicks'] = 'क्लिक';
$string['report:aggregation'] = 'समूहीकरण';
$string['report:aggregationtype'] = 'विंडो के भीतर समान प्रतिक्रिया';
$string['report:aggregationpeak'] = 'किसी भी प्रतिक्रिया का शिखर';
$string['report:exportcsv'] = 'CSV निर्यात करें';
$string['progress'] = 'प्रगति';
$string['uniquereactions'] = 'भिन्न प्रतिक्रियाएँ';
$string['removereaction'] = 'प्रतिक्रिया हटाएँ';
$string['playerunavailable'] = 'प्लेयर प्रारंभ नहीं किया जा सका।';
$string['yes'] = 'हाँ';
$string['no'] = 'नहीं';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heading_performance'] = 'प्रदर्शन';
$string['setting:heading_accessibility'] = 'सुगम्यता';
$string['setting:heading_accessibility_desc'] = 'सहायक तकनीक घोषणाओं और कीबोर्ड/स्क्रीन रीडर प्रतिक्रिया की सेटिंग्स।';
$string['setting:heading_defaults'] = 'नई गतिविधियों के लिए डिफ़ॉल्ट मान';
$string['setting:heading_defaults_desc'] = 'ये मान डिफ़ॉल्ट के रूप में उपयोग किए जाते हैं जब कोई शिक्षक नई VideoTrack गतिविधि बनाता है। प्रत्येक गतिविधि को फिर भी व्यक्तिगत रूप से कॉन्फ़िगर किया जा सकता है।';
$string['setting:default_desc'] = 'नई गतिविधियों के लिए डिफ़ॉल्ट मान। शिक्षक द्वारा प्रत्येक गतिविधि के लिए ओवरराइड किया जा सकता है।';
$string['setting:default_completionpercent_desc'] = 'वीडियो का डिफ़ॉल्ट न्यूनतम प्रतिशत जो छात्र को गतिविधि पूरी करने के लिए देखना होगा। 0 सेट करें ताकि पूर्णता नियम डिफ़ॉल्ट रूप से अक्षम रहे।';
$string['event:segment_saved'] = 'व्यूइंग सेगमेंट सहेजा गया';
$string['event:reaction_saved'] = 'प्रतिक्रिया सबमिट की गई';
$string['event:note_saved'] = 'छात्र नोट सहेजा गया';
$string['event:note_deleted'] = 'व्यक्तिगत नोट हटाया गया';
$string['event:reaction_deleted'] = 'प्रतिक्रिया हटाई गई';
$string['setting:heartbeatinterval'] = 'हार्टबीट अंतराल (सेकंड)';
$string['setting:heartbeatinterval_desc'] = 'निरंतर प्लेबैक के दौरान प्लेयर कितनी बार सर्वर पर वर्तमान व्यूइंग सेगमेंट सहेजता है। कम मान ब्राउज़र क्रैश या नेटवर्क विफलता पर डेटा हानि के जोखिम को कम करते हैं, लेकिन सर्वर लोड बढ़ाते हैं (प्रति छात्र प्रति अंतराल एक AJAX अनुरोध + दो डेटाबेस क्वेरी)। अनुशंसित सीमा: 15–120 सेकंड। न्यूनतम लागू मान: 5 सेकंड (5 से कम मान स्वचालित रूप से सर्वर द्वारा 5 कर दिए जाते हैं)।';
$string['setting:reportclusterlimit'] = 'संचयी रिपोर्ट क्लस्टर सीमा';
$string['setting:reportclusterlimit_desc'] = 'संचयी रिपोर्ट में दिखाए जाने वाले प्रतिक्रिया क्लस्टरों की अधिकतम संख्या, जिसके बाद शिक्षकों से फ़िल्टर सीमित करने को कहा जाता है। बड़े डेटासेट पर ऊँचे मान अधिक विस्तृत विश्लेषण देते हैं, लेकिन दिखाते और निर्यात करते समय अधिक मेमोरी उपयोग करते हैं।';
$string['setting:reportnotespagesize'] = 'छात्र नोट पृष्ठ आकार';
$string['setting:reportnotespagesize_desc'] = 'छात्र नोट रिपोर्ट में प्रति पृष्ठ दिखाए जाने वाले व्यक्तिगत नोट्स की संख्या। कम मान बड़े पाठ्यक्रमों में मेमोरी उपयोग घटाते हैं; अधिक मान पृष्ठों की संख्या घटाते हैं। डिफ़ॉल्ट: 100।';
$string['setting:reactionannouncementinterval'] = 'प्रतिक्रिया सुगम्यता घोषणा अंतराल (मिलीसेकंड)';
$string['setting:reactionannouncementinterval_desc'] = 'स्क्रीन रीडर के लिए दोहराई गई “प्रतिक्रियाएँ उपलब्ध नहीं हैं” घोषणाओं के बीच न्यूनतम अंतराल, मिलीसेकंड में। छोटे वीडियो में अधिक बार प्रतिक्रिया के लिए कम मान या दोहराई गई घोषणाएँ कम करने के लिए अधिक मान उपयोग करें। दोहराई गई घोषणाएँ बंद करने के लिए 0 सेट करें। सक्रिय होने पर अनुशंसित सीमा: 10000–60000 मिलीसेकंड। उदाहरण: 10000 = 10 सेकंड, 30000 = 30 सेकंड, 60000 = 1 मिनट।';
$string['setting:reactionreadydebouncems'] = 'तैयार प्रतिक्रियाओं का डिबाउंस (मिलीसेकंड)';

$string['setting:statusinfotimeoutms'] = 'Status message timeout (milliseconds)';
$string['setting:statusinfotimeoutms_desc'] = 'How long informational player status messages remain visible before auto-dismissal. Recommended range: 4000–20000 milliseconds.';
$string['setting:statuserrortimeoutms'] = 'Error status timeout (milliseconds)';
$string['setting:statuserrortimeoutms_desc'] = 'How long player error messages remain visible before auto-dismissal. Use a longer timeout to improve accessibility and recovery time. Recommended range: 6000–30000 milliseconds.';
$string['setting:reactionreadydebouncems_desc'] = 'तेज़ विराम और फिर से शुरू करने के बाद “प्रतिक्रियाएँ उपलब्ध हैं” घोषणा दोहराने से पहले न्यूनतम विलंब, मिलीसेकंड में। यह डिबाउंस बंद करने के लिए 0 सेट करें।';

$string['reactionx'] = 'प्रतिक्रिया {$a}';

$string['err:reactioniconfilerequired'] = 'जब आइकन प्रकार अपलोड की गई फ़ाइल हो, तब एक आइकन फ़ाइल अपलोड करें।';


$string['privacy:metadata:common:timecreated'] = 'रिकॉर्ड बनाए जाने का समय।';
$string['privacy:metadata:common:timemodified'] = 'रिकॉर्ड में अंतिम परिवर्तन का समय।';

$string['privacy:metadata:common:videotrackid'] = 'रिकॉर्ड से जुड़ी VideoTrack गतिविधि का आंतरिक पहचानकर्ता।';
$string['privacy:metadata:common:courseid'] = 'गतिविधि से जुड़े पाठ्यक्रम का पहचानकर्ता।';
$string['privacy:metadata:common:cmid'] = 'गतिविधि से जुड़े कोर्स मॉड्यूल का पहचानकर्ता।';
$string['privacy:metadata:common:videoid'] = 'गतिविधि के लिए कॉन्फ़िगर किए गए वीडियो या सामग्री का पहचानकर्ता।';
$string['privacy:metadata:videotrack_reactev:reactionid'] = 'इवेंट रिकॉर्ड करते समय उपयोग की गई प्रतिक्रिया परिभाषा का आंतरिक पहचानकर्ता।';
$string['privacy:metadata:external:ipaddress'] = 'सामान्य ब्राउज़र अनुरोधों के हिस्से के रूप में बाहरी प्रदाता दर्शक का IP पता प्राप्त कर सकता है।';
$string['privacy:metadata:external:cookies'] = 'बाहरी प्रदाता अपनी गोपनीयता नीति और ब्राउज़र सेटिंग के अनुसार कुकी सेट या पढ़ सकता है।';
$string['privacy:metadata:external:useragent'] = 'बाहरी प्रदाता ब्राउज़र और डिवाइस की जानकारी, जैसे user-agent header, प्राप्त कर सकता है।';
$string['privacy:metadata:videotrack_seg'] = 'वीडियो गतिविधि में किसी उपयोगकर्ता के लिए रिकॉर्ड किए गए देखने के खंड संग्रहीत करता है।';
$string['privacy:metadata:videotrack_seg:userid'] = 'वह उपयोगकर्ता जिसका देखने का खंड रिकॉर्ड किया गया।';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'देखने के खंड से जुड़ा ब्राउज़र सत्र पहचानकर्ता।';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'सर्वर समय जब खंड शुरू हुआ।';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'सर्वर समय जब खंड समाप्त हुआ।';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'खंड की शुरुआत में वीडियो टाइमलाइन की स्थिति।';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'खंड के अंत में वीडियो टाइमलाइन की स्थिति।';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'खंड के दौरान उपयोग की गई प्लेबैक गति।';
$string['privacy:metadata:videotrack_seg:endreason'] = 'खंड समाप्त होने का कारण।';
$string['privacy:metadata:videotrack_state'] = 'वीडियो गतिविधि में किसी उपयोगकर्ता की समेकित देखने की स्थिति संग्रहीत करता है।';
$string['privacy:metadata:videotrack_state:userid'] = 'वह उपयोगकर्ता जिसकी समेकित स्थिति संग्रहीत की गई।';
$string['privacy:metadata:videotrack_state:lastposition'] = 'वीडियो टाइमलाइन में उपयोगकर्ता द्वारा पहुँची अंतिम ज्ञात स्थिति।';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'ट्रैक किए गए वीडियो की अवधि सेकंड में।';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'उपयोगकर्ता द्वारा कवर किए गए टाइमलाइन के अद्वितीय सेकंड की संख्या।';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'उपयोगकर्ता के लिए गणना किया गया पूर्णता प्रतिशत।';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'अद्वितीय कवरेज की गणना के लिए उपयोग किए गए मर्ज किए गए अंतराल।';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'क्या गतिविधि वर्तमान में उपयोगकर्ता के लिए पूर्ण चिह्नित है।';
$string['privacy:metadata:videotrack_reactev'] = 'वीडियो देखते समय रिकॉर्ड किए गए प्रतिक्रिया इवेंट संग्रहीत करता है।';
$string['privacy:metadata:videotrack_reactev:userid'] = 'वह उपयोगकर्ता जिसने प्रतिक्रिया भेजी।';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'प्रतिक्रिया इवेंट से जुड़ा ब्राउज़र सत्र पहचानकर्ता।';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'रिकॉर्डिंग के समय प्रतिक्रिया की आंतरिक कुंजी।';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'रिकॉर्डिंग के समय उपयोगकर्ता को दिखाई गई प्रतिक्रिया लेबल।';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'रिकॉर्डिंग के समय उपयोगकर्ता को दिखाई गई प्रतिक्रिया विवरण।';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'प्रतिक्रिया रिकॉर्ड होने पर वीडियो टाइमलाइन की स्थिति।';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'प्रतिक्रिया रिकॉर्ड होने पर प्लेबैक गति।';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'बताता है कि क्या प्रतिक्रिया घटना उपयोगकर्ता द्वारा हटाई गई थी।';

$string['videotrack:viewcoursereport'] = 'कोर्स-स्तर की VideoTrack रिपोर्ट देखें';
$string['videotrack:viewcoursereport_desc'] = 'पूरे कोर्स के लिए समेकित VideoTrack रिपोर्ट देखने की अनुमति देता है।';
$string['videotrack:overrideplayersettings'] = 'प्लेटफ़ॉर्म प्लेयर सेटिंग्स को ओवरराइड करें';
$string['videotrack:overrideplayersettings_desc'] = 'शिक्षक को प्रशासक द्वारा निर्धारित प्लेटफ़ॉर्म-व्यापी प्लेयर सेटिंग्स बदलने की अनुमति देता है।';
$string['videotrack:overridecompletionsettings'] = 'प्लेटफ़ॉर्म पूर्णता सेटिंग्स को ओवरराइड करें';
$string['videotrack:managereactions'] = 'VideoTrack प्रतिक्रिया परिभाषाएँ प्रबंधित करें';
$string['videotrack:managereactions_desc'] = 'शिक्षक को VideoTrack गतिविधि बनाते या संपादित करते समय प्रतिक्रिया परिभाषाएँ जोड़ने, संपादित करने और हटाने की अनुमति देता है।';
$string['videotrack:grade'] = 'VideoTrack सबमिशन ग्रेड करें';
$string['videotrack:grade_desc'] = 'उपयोगकर्ता को छात्रों की VideoTrack देखने की प्रगति को ग्रेड असाइन करने की अनुमति देता है।';
$string['videotrack:overridecompletionsettings_desc'] = 'शिक्षक को प्रशासक द्वारा निर्धारित प्लेटफ़ॉर्म-व्यापी पूर्णता सेटिंग्स बदलने की अनुमति देता है।';
$string['setting:lockedbyAdmin'] = 'ये सेटिंग्स प्लेटफ़ॉर्म प्रशासक द्वारा लॉक की गई हैं और व्यक्तिगत गतिविधियों के लिए नहीं बदली जा सकतीं।';
$string['setting:heading_presets'] = 'प्रतिक्रिया प्रीसेट';
$string['setting:heading_presets_desc'] = 'साइट-व्यापी प्रतिक्रिया सेट जिन्हें शिक्षक शुरुआती बिंदु के रूप में उपयोग कर सकते हैं।';
$string['reactionpreset'] = 'प्रतिक्रिया प्रीसेट लागू करें';
$string['reactionpreset_help'] = 'नीचे प्रतिक्रिया फ़ील्ड को पूर्व-भरने के लिए एक प्रीसेट चुनें। बाद में मान स्वतंत्र रूप से संपादित किए जा सकते हैं।';
$string['reactionpreset:none'] = '— मैन्युअल रूप से कॉन्फ़िगर करें —';
$string['presets:manage'] = 'प्रतिक्रिया प्रीसेट प्रबंधित करें';
$string['presets:pagetitle'] = 'VideoTrack — प्रतिक्रिया प्रीसेट';
$string['presets:intro'] = 'साइट-व्यापी प्रतिक्रिया प्रीसेट परिभाषित करें जिन्हें शिक्षक VideoTrack गतिविधि बनाते समय शुरुआती बिंदु के रूप में उपयोग कर सकते हैं। प्रतिक्रियाएँ गतिविधि में कॉपी की जाती हैं और शिक्षक उन्हें स्वतंत्र रूप से संपादित कर सकते हैं।';
$string['presets:addpreset'] = 'प्रीसेट जोड़ें';
$string['presets:backtolist'] = 'प्रीसेट सूची पर वापस जाएं';
$string['presets:saved'] = 'प्रीसेट सहेजा गया।';
$string['presets:deleted'] = 'प्रीसेट हटाया गया।';
$string['presets:notfound'] = 'प्रीसेट नहीं मिला।';
$string['presets:noneyet'] = 'अभी तक कोई प्रतिक्रिया प्रीसेट कॉन्फ़िगर नहीं किया गया है।';
$string['presets:confirmdelete'] = 'क्या आप इस प्रीसेट को हटाना चाहते हैं?';
$string['confirmfallback'] = 'पुष्टिकरण संवाद नहीं खोला जा सका। कृपया फिर से प्रयास करें।';
$string['presets:presetdetails'] = 'प्रीसेट विवरण';
$string['presets:name'] = 'प्रीसेट नाम';
$string['presets:key'] = 'प्रीसेट कुंजी';
$string['presets:key_help'] = 'अद्वितीय पहचानकर्ता (केवल अक्षर, संख्याएं और अंडरस्कोर)। निर्माण के बाद नहीं बदला जा सकता।';
$string['presets:reactions'] = 'प्रतिक्रियाएं';
$string['presets:reactions_help'] = 'किसी पंक्ति को छोड़ने के लिए लेबल खाली छोड़ें।';
$string['presets:col_name'] = 'नाम';
$string['presets:col_key'] = 'कुंजी';
$string['presets:col_reactions'] = 'प्रतिक्रियाएं';
$string['presets:col_actions'] = 'क्रियाएं';

$string['reset:userdata'] = 'सभी छात्र दृश्य डेटा हटाएं (सेगमेंट, स्थिति, प्रतिक्रियाएं)';
$string['report:recalculate'] = 'सभी पूर्णता स्थितियों की पुनर्गणना करें';
$string['report:recalculated'] = '{$a} उपयोगकर्ताओं के लिए पूर्णता स्थितियों की पुनर्गणना की गई।';
$string['report:heatmap_desc'] = 'वीडियो टाइमलाइन पर प्रतिक्रिया हीटमैप (बार की ऊंचाई = उस बिंदु पर क्लिक की संख्या):';
$string['report:heatmap_supplementary'] = 'हीटमैप एक पूरक दृश्य प्रस्तुति है। क्लस्टर के पूर्ण डेटा नीचे दी गई तालिका में उपलब्ध हैं.';
$string['event:activity_completed'] = 'VideoTrack गतिविधि पूर्ण';

$string['reactioniconfile_notice'] = 'छवि को स्वचालित रूप से 64×64 पिक्सेल (केंद्र क्रॉप) में बदल दिया जाएगा। सर्वोत्तम परिणामों के लिए, एक वर्गाकार छवि (1:1 अनुपात) अपलोड करें। स्वीकृत प्रारूप: JPG, PNG, GIF, WebP।';
$string['reactions_hint'] = 'वीडियो चलते समय उस क्षण अपनी प्रतिक्रिया दर्ज करने के लिए किसी प्रतिक्रिया बटन पर क्लिक करें।';

$string['showgradeto'] = 'छात्र को ग्रेड दिखाएं';
$string['showgradeto_help'] = 'यदि सक्षम है, तो छात्र सीधे गतिविधि पृष्ठ पर अपना ग्रेड देखेगा।';
$string['report:grade'] = 'ग्रेड';
$string['report:gradesaved'] = 'ग्रेड सफलतापूर्वक सहेजा गया।';
$string['report:gradepass_hint'] = 'उत्तीर्ण अंक: {$a}';
$string['report:gradenotset'] = 'अभी तक मूल्यांकन नहीं हुआ';

$string['videosource'] = 'वीडियो स्रोत';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'अपलोड (MP4/WebM/MP3)';
$string['vimeourl'] = 'Vimeo URL';
$string['vimeourl_help'] = 'Vimeo वीडियो URL पेस्ट करें (उदाहरण: https://vimeo.com/123456789)।';
$string['invalidvimeourl'] = 'मान्य Vimeo URL नहीं।';
$string['videofile'] = 'वीडियो/ऑडियो फ़ाइल';
$string['videofile_help'] = 'MP4, WebM या MP3 अपलोड करें।';
$string['videofile_notice'] = 'स्वीकृत प्रारूप: MP4, WebM, MP3, M4V, MOV, AAC, M4A।';
$string['setting:heading_player'] = 'प्लेयर व्यवहार';
$string['setting:playbackspeeds'] = 'उपलब्ध प्लेबैक गति';
$string['setting:playbackspeeds_desc'] = 'चुनें कि साइट पर कौन सी प्लेबैक गति उपलब्ध हों। शिक्षक अलग-अलग गतिविधियों के लिए इस सूची को सीमित कर सकते हैं (यदि उनके पास override क्षमता है)। 1× (सामान्य) मान हमेशा अनुशंसित है।';
$string['setting:playbackspeeds_teacher_desc'] = 'इस गतिविधि के लिए उपलब्ध प्लेबैक गति चुनें। केवल साइट स्तर पर सक्षम गति दिखाई जाती हैं। साइट का डिफ़ॉल्ट उपयोग करने के लिए सभी चुने रहने दें।';
$string['setting:speed_normal'] = 'सामान्य';
$string['setting:distractionfree'] = 'ध्यान-भटकाव मुक्त मोड';
$string['setting:distractionfree_desc'] = 'देखते समय Moodle हेडर, फुटर और नेविगेशन छुपाता है।';
$string['intervalbar_title'] = 'देखे गए अंतराल — हरे खंड पहले से देखे गए भाग हैं।';
$string['outline:percent'] = '{$a}% देखा गया';
$string['outline:nodata'] = 'कोई डेटा नहीं।';
$string['coursereport:title'] = 'VideoTrack — पाठ्यक्रम रिपोर्ट';
$string['coursereport:navlink'] = 'VideoTrack रिपोर्ट';
$string['coursereport:intro'] = 'पाठ्यक्रम में सभी VideoTrack गतिविधियों का अवलोकन।';
$string['coursereport:nodata'] = 'कोई VideoTrack गतिविधि नहीं मिली।';
$string['coursereport:col_activity'] = 'गतिविधि';
$string['coursereport:col_source'] = 'स्रोत';
$string['coursereport:col_duration'] = 'अवधि';
$string['coursereport:col_students_started'] = 'शुरू किए छात्र';
$string['coursereport:col_avg_percent'] = 'औसत कवरेज';
$string['coursereport:col_completions'] = 'पूर्णताएं';
$string['coursereport:col_reactions'] = 'प्रतिक्रियाएं';
$string['coursereport:col_actions'] = 'क्रियाएं';

$string['grade:pass'] = 'उत्तीर्ण';
$string['grade:fail'] = 'अनुत्तीर्ण';

$string['autoplay'] = 'स्वचालित प्लेबैक';
$string['autoplay_help'] = 'पेज लोड होने पर वीडियो स्वचालित रूप से शुरू करें। ब्राउज़र को ऑटोप्ले के लिए म्यूट की आवश्यकता है।';
$string['loop'] = 'लूप में दोहराएं';
$string['startmuted'] = 'म्यूट से शुरू करें';
$string['startmuted_help'] = 'ऑडियो बंद करके प्लेबैक शुरू करें। छात्र मैन्युअल रूप से म्यूट हटा सकते हैं। आधुनिक ब्राउज़रों में ऑटोप्ले के लिए आवश्यक।';
$string['allowdownload'] = 'डाउनलोड की अनुमति दें (केवल अपलोड स्रोत)';
$string['setting:allowdownload_desc'] = 'HTML5 प्लेयर में डाउनलोड बटन दिखाएं।';
$string['setting:heading_playerbehavior'] = 'डिफ़ॉल्ट प्लेयर व्यवहार';
$string['setting:heading_playerbehavior_desc'] = 'नई गतिविधियों के लिए ऑटोप्ले, लूप, म्यूट और डाउनलोड के डिफ़ॉल्ट मान।';
$string['setting:heading_html5controls'] = 'HTML5 प्लेयर नियंत्रण (अपलोड स्रोत)';
$string['setting:heading_html5controls_desc'] = 'कस्टम HTML5 प्लेयर बार में उपलब्ध नियंत्रण चुनें। यह केवल Upload वीडियो स्रोत वाली गतिविधियों पर लागू होता है। शिक्षक अलग-अलग गतिविधियों के लिए इस सूची को सीमित कर सकते हैं।';
$string['setting:html5controls'] = 'उपलब्ध नियंत्रण';
$string['setting:html5controls_desc'] = 'HTML5 प्लेयर में दिखाने के लिए नियंत्रण चुनें।';
$string['setting:html5controls_teacher_desc'] = 'इस गतिविधि के लिए प्लेयर में कौन से नियंत्रण दिखाने हैं चुनें। केवल साइट स्तर पर सक्षम नियंत्रण दिखाए जाते हैं।';
$string['ctrl:play'] = 'प्ले/पॉज़';
$string['ctrl:progress'] = 'प्रगति बार';
$string['ctrl:current'] = 'वर्तमान समय';
$string['ctrl:duration'] = 'अवधि';
$string['ctrl:mute'] = 'म्यूट';
$string['ctrl:volume'] = 'वॉल्यूम';
$string['ctrl:speed'] = 'गति';
$string['ctrl:pip'] = 'पिक्चर-इन-पिक्चर';
$string['ctrl:fullscreen'] = 'पूर्ण स्क्रीन';
$string['ctrl:download'] = 'डाउनलोड';

$string['setting:playerwidth'] = 'अधिकतम प्लेयर चौड़ाई (px)';
$string['setting:playerwidth_desc'] = 'वीडियो प्लेयर की अधिकतम चौड़ाई पिक्सेल में (1–4096)। शिक्षक अलग-अलग गतिविधियों के लिए इसे बदल सकते हैं (गतिविधि में 0 = साइट का डिफ़ॉल्ट उपयोग करें)। अनुशंसित: 960.';
$string['playerwidth'] = 'अधिकतम प्लेयर चौड़ाई (px)';
$string['playerwidth_help'] = 'इस गतिविधि के लिए प्लेयर की अधिकतम चौड़ाई। 0 = साइट डिफ़ॉल्ट।';
$string['playerwidth_zero_note'] = 'प्लेटफ़ॉर्म डिफ़ॉल्ट अपनाने के लिए 0 दर्ज करें, या इस गतिविधि के लिए 1 से 4096 पिक्सेल तक का मान दर्ज करें।';
$string['setting:rewindstep'] = 'रिवाइंड चरण (सेकंड)';
$string['setting:rewindstep_desc'] = 'रिवाइंड बटन डिफ़ॉल्ट रूप से कितने सेकंड पीछे जाता है। शिक्षक अलग-अलग गतिविधियों में इसे बदल सकते हैं। 0 सेट करने पर बटन डिफ़ॉल्ट रूप से छिपेगा; गतिविधि का अपना मान उसे फिर से दिखा सकता है। डिफ़ॉल्ट: 10। महत्वपूर्ण: यदि गतिविधि में "पीछे जाने की अनुमति" बंद है, तो यह मान > 0 होने पर भी बटन नहीं दिखेगा।';
$string['rewindstep'] = 'रिवाइंड चरण (सेकंड)';
$string['rewindstep_help'] = 'इस गतिविधि में रिवाइंड बटन कितने सेकंड पीछे जाता है। प्लेटफ़ॉर्म डिफ़ॉल्ट उपयोग करने के लिए 0 छोड़ें। यदि प्लेटफ़ॉर्म डिफ़ॉल्ट 0 है, तो बटन छिपा रहेगा जब तक यह गतिविधि अपना मान सेट नहीं करती। नोट: यदि इस गतिविधि में "पीछे जाने की अनुमति" बंद है, तो यह मान कुछ भी हो, बटन नहीं दिखेगा।';
$string['setting:fastforwardstep'] = 'फास्ट-फॉरवर्ड चरण (सेकंड)';
$string['setting:fastforwardstep_desc'] = 'फास्ट-फॉरवर्ड बटन डिफ़ॉल्ट रूप से कितने सेकंड आगे जाता है। शिक्षक अलग-अलग गतिविधियों में इसे बदल सकते हैं। 0 सेट करने पर बटन डिफ़ॉल्ट रूप से छिपेगा; गतिविधि का अपना मान उसे फिर से दिखा सकता है। डिफ़ॉल्ट: 10। महत्वपूर्ण: यदि गतिविधि में "आगे जाने की अनुमति" बंद है, तो यह मान > 0 होने पर भी बटन नहीं दिखेगा।';
$string['fastforwardstep'] = 'फास्ट-फॉरवर्ड चरण (सेकंड)';
$string['fastforwardstep_help'] = 'इस गतिविधि में फास्ट-फॉरवर्ड बटन कितने सेकंड आगे जाता है। प्लेटफ़ॉर्म डिफ़ॉल्ट उपयोग करने के लिए 0 छोड़ें। यदि प्लेटफ़ॉर्म डिफ़ॉल्ट 0 है, तो बटन छिपा रहेगा जब तक यह गतिविधि अपना मान सेट नहीं करती। नोट: यदि इस गतिविधि में "आगे जाने की अनुमति" बंद है, तो यह मान कुछ भी हो, बटन नहीं दिखेगा।';
$string['captionsheader'] = 'उपशीर्षक';
$string['captions'] = 'उपशीर्षक सक्षम करें';
$string['captions_help'] = 'सक्षम होने पर: YouTube — उपशीर्षक डिफ़ॉल्ट रूप से दिखाए जाते हैं; Vimeo — भाषा कोड से मेल खाने वाली ट्रैक सक्रिय होती है (Vimeo.com पर पहले से लोड होनी चाहिए); Upload — संलग्न VTT फ़ाइल उपयोग की जाती है।';
$string['setting:default_captions_desc'] = 'नई गतिविधियों के लिए डिफ़ॉल्ट रूप से उपशीर्षक सक्षम करें।';
$string['captionslang'] = 'डिफ़ॉल्ट उपशीर्षक भाषा';
$string['captionslang_help'] = 'ISO 639-1 भाषा कोड (जैसे hi, en, de)। YouTube के लिए: पसंदीदा उपशीर्षक भाषा सेट करता है। Vimeo के लिए: मेल खाने वाली ट्रैक सक्रिय करता है (Vimeo.com पर लोड होनी चाहिए)। Upload के लिए: जानकारी फ़ील्ड।';
$string['setting:captionslang_desc'] = 'डिफ़ॉल्ट उपशीर्षक भाषा (ISO 639-1)।';
$string['vttfile'] = 'उपशीर्षक फ़ाइल (.vtt)';
$string['vttfile_help'] = 'WebVTT (.vtt) उपशीर्षक फ़ाइल अपलोड करें। फ़ाइल छात्र के ब्राउज़र को भेजी जाएगी और वीडियो प्लेयर में उपशीर्षक के रूप में दिखाई जाएगी।';
$string['vttfile_notice'] = 'स्वीकृत प्रारूप: WebVTT (.vtt)। केवल एक फ़ाइल समर्थित है। फ़ाइल ऊपर बताए गए भाषा कोड से मेल खानी चाहिए।';
$string['vimeo_captions_notice'] = 'Vimeo उपशीर्षक Vimeo.com पर प्रबंधित किए जाते हैं। वहाँ अपनी उपशीर्षक ट्रैक अपलोड करें। ऊपर दिया गया भाषा कोड मेल खाने वाली ट्रैक को स्वचालित रूप से सक्रिय करने के लिए उपयोग होगा।';
$string['ctrl:rewind'] = 'रिवाइंड बटन';
$string['ctrl:fastforward'] = 'फास्ट-फॉरवर्ड बटन';

$string['playerloading'] = 'वीडियो प्लेयर लोड हो रहा है, कृपया प्रतीक्षा करें…';
$string['noreactionsyet'] = 'अभी तक कोई प्रतिक्रिया दर्ज नहीं हुई। वीडियो चलते समय प्रतिक्रिया दें।';
$string['reaction:error'] = 'आपकी प्रतिक्रिया सहेजी नहीं जा सकी। कृपया पुनः प्रयास करें।';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'प्लेबैक फिर से शुरू करें';
$string['resumeplayback_desc'] = 'विद्यार्थी ने पिछली सत्र में जहाँ छोड़ा था, वहाँ से वीडियो स्वतः फिर शुरू करता है।';
$string['resumeplayback_help'] = 'सक्षम होने पर वीडियो अंतिम सहेजी गई स्थिति से शुरू होगा (यदि वह वीडियो की शुरुआत से 5 सेकंड से अधिक है)। विद्यार्थी हमेशा मैन्युअल रूप से शुरुआत पर जा सकते हैं।';
$string['setting:resumeplayback'] = 'प्लेबैक फिर से शुरू करें (डिफ़ॉल्ट)';
$string['setting:resumeplayback_desc'] = 'नई VideoTrack गतिविधियों के लिए डिफ़ॉल्ट सेटिंग। शिक्षक इसे प्रति गतिविधि बदल सकते हैं।';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'अधिकतम प्लेबैक गति';
$string['maxplaybackrate_desc'] = 'विद्यार्थियों द्वारा चुनी जा सकने वाली अधिकतम वीडियो गति सीमित करें। 0 = कोई सीमा नहीं।';
$string['maxplaybackrate_help'] = 'सेट होने पर विद्यार्थी इस गति से अधिक तेज़ वीडियो नहीं चला सकते, भले ही प्लेयर नियंत्रण उच्च मान दिखाएँ।';
$string['maxplaybackrate_nolimit'] = 'कोई सीमा नहीं';
$string['setting:maxplaybackrate'] = 'अधिकतम प्लेबैक गति (डिफ़ॉल्ट)';
$string['setting:maxplaybackrate_desc'] = 'नई गतिविधियों के लिए डिफ़ॉल्ट अधिकतम गति। शिक्षक इसे प्रति गतिविधि बदल सकते हैं।';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'इंटरैक्टिव ट्रांसक्रिप्ट दिखाएँ';
$string['showtranscript_desc'] = 'वीडियो के पास स्क्रॉल और क्लिक की जा सकने वाली ट्रांसक्रिप्ट दिखाता है (VTT उपशीर्षक फ़ाइल आवश्यक)।';
$string['showtranscript_help'] = 'अपलोड की गई VTT फ़ाइल को पढ़कर क्लिक करने योग्य सूची के रूप में दिखाता है। हर प्रविष्टि में टाइमस्टैम्प और पाठ होता है; क्लिक करने पर वीडियो उस स्थान पर जाता है।';
$string['transcript_title'] = 'ट्रांसक्रिप्ट';
$string['transcript_unavailable'] = 'इस वीडियो के लिए ट्रांसक्रिप्ट उपलब्ध नहीं है।';
$string['transcript_loading'] = 'ट्रांसक्रिप्ट लोड हो रही है…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'प्लेबैक शुरू करने के लिए वीडियो पर क्लिक करें।';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['sdkerrorlabel'] = 'वीडियो प्लेयर लोड नहीं हो सका। इसका कारण ad-blocker, Content Security Policy या नेटवर्क प्रतिबंध हो सकता है। कंटेंट ब्लॉकर बंद करें या व्यवस्थापक से संपर्क करें।';
$string['vimeocspwarnlabel'] = 'Vimeo प्लेयर लोड नहीं हो सका। नेटवर्क कनेक्शन जाँचें या व्यवस्थापक से Content Security Policy में player.vimeo.com की अनुमति देने को कहें।';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'यहाँ से फिर शुरू';
// ── Report: azioni studente ──
$string['report:actions'] = 'कार्रवाइयाँ';
$string['report:resetstudent'] = 'प्रगति रीसेट करें';
$string['report:resetstudent_confirm'] = 'क्या आप वाकई इस विद्यार्थी की प्रगति रीसेट करना चाहते हैं? देखने का इतिहास और प्रतिक्रियाएँ हट जाएँगी और यह वापस नहीं किया जा सकेगा।';
$string['report:studentreset'] = 'विद्यार्थी की प्रगति रीसेट कर दी गई है।';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'अध्याय नेविगेशन दिखाएँ';
$string['showchapters_desc'] = 'VTT फ़ाइल से निकाले गए अध्याय मार्करों वाली नेविगेशन पट्टी दिखाता है। अध्याय 80 अक्षरों से कम पाठ वाले VTT cues हैं।';
$string['showchapters_help'] = 'यदि अपलोड की गई VTT फ़ाइल में छोटे cues (80 अक्षरों से कम) हैं, तो उन्हें अध्याय शीर्षक माना जाता है और वीडियो नियंत्रणों के ऊपर क्लिक करने योग्य नेविगेशन पट्टी में दिखाया जाता है। किसी अध्याय पर क्लिक करने से वीडियो उस स्थान पर चला जाता है।';
$string['chapters_label'] = 'वीडियो अध्याय';
$string['chapters_unavailable'] = 'इस वीडियो के लिए अध्याय उपलब्ध नहीं हैं।';
$string['chapter_label'] = 'अध्याय';
$string['studentnotesenabled'] = 'विद्यार्थी नोट्स सक्षम करें';
$string['studentnotesenabled_desc'] = 'विद्यार्थियों को वीडियो देखते समय टाइमस्टैम्प वाली निजी नोट्स लिखने दें।';
$string['studentnotesenabled_help'] = 'सक्षम होने पर वीडियो के पास पाठ क्षेत्र दिखता है। विद्यार्थी वर्तमान वीडियो टाइमस्टैम्प पर नोट सहेज सकते हैं। नोट्स केवल लिखने वाले विद्यार्थी और रिपोर्ट में प्रबंधकों को दिखते हैं।';
$string['setting:studentnotesenabled'] = 'विद्यार्थी नोट्स सक्षम करें (डिफ़ॉल्ट)';
$string['setting:studentnotesenabled_desc'] = 'नई VideoTrack गतिविधियों के लिए डिफ़ॉल्ट सेटिंग। शिक्षक इसे प्रति गतिविधि बदल सकते हैं।';
$string['setting:notemaxlength'] = 'नोट की अधिकतम लंबाई';
$string['setting:notemaxlength_desc'] = 'हर व्यक्तिगत विद्यार्थी नोट के लिए अनुमत अधिकतम वर्ण संख्या। डिफ़ॉल्ट: 2000.';
$string['studentnotes_title'] = 'मेरे नोट्स';
$string['studentnote_placeholder'] = 'वीडियो के इस क्षण पर नोट लिखें…';
$string['studentnote_save'] = 'नोट सहेजें';
$string['studentnote_hint'] = 'नोट वर्तमान वीडियो टाइमस्टैम्प पर सहेजा जाएगा। वीडियो चल रहा होना चाहिए।';
$string['studentnotes_list_label'] = 'सहेजे गए नोट्स';
$string['studentnote_label'] = 'विद्यार्थी नोट';
$string['noteerrorlabel'] = 'नोट सहेजा नहीं जा सका। कृपया फिर प्रयास करें।';
$string['notesavedlabel'] = 'नोट सहेजा गया।';
$string['notedeletedlabel'] = 'नोट हटाया गया।';
$string['noteemptylabel'] = 'नोट खाली है। सहेजने से पहले एक नोट लिखें।';
$string['notetoolonglabel'] = 'नोट साइट द्वारा अनुमत अधिकतम लंबाई से अधिक है।';
$string['studentnoteslimitedlabel'] = 'केवल नवीनतम {$a} नोट दिखाए जा रहे हैं।';
$string['noteplaybackrequiredlabel'] = 'नोट सहेजने से पहले प्लेबैक शुरू करें।';
$string['charsremaininglabel'] = 'अक्षर शेष';
$string['posterimage'] = 'पोस्टर / पूर्वावलोकन छवि';
$string['posterimage_help'] = 'वीडियो शुरू होने से पहले दिखाने के लिए एक छवि अपलोड करें। स्वीकृत प्रारूप: JPG, PNG, WebP, GIF। अनुशंसित आकार: 1280×720 px (16:9)।';
$string['posterimage_notice'] = 'पोस्टर छवि प्लेबैक शुरू होने से पहले दिखती है और वीडियो चलने पर स्वतः छिप जाती है।';
$string['playbutton_label'] = 'वीडियो चलाएँ';
$string['setting:maxplaybackrate_nolimit'] = 'कोई सीमा नहीं';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'वीडियो के किसी विशिष्ट टाइमस्टैम्प पर विद्यार्थी द्वारा लिखे गए निजी नोट का पाठ।';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'इवेंट प्रकार: मानक प्रतिक्रियाओं के लिए खाली, विद्यार्थी निजी नोट्स के लिए "note"।';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['reactionsdisabled'] = 'इस VideoTrack गतिविधि के लिए प्रतिक्रियाएँ अक्षम हैं। यदि प्रतिक्रियाएँ आवश्यक हैं, तो अपने शिक्षक या कोर्स व्यवस्थापक से उन्हें सक्षम करने के लिए कहें।';
$string['studentnotesdisabled'] = 'इस गतिविधि के लिए विद्यार्थी नोट्स सक्षम नहीं हैं।';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'इस गतिविधि के लिए कोई वीडियो फ़ाइल अपलोड नहीं की गई है।';
$string['removenote'] = 'नोट हटाएँ';
// ── Note toggle + report note ──
$string['notes_hide'] = 'नोट्स छिपाएँ';
$string['notes_show'] = 'नोट्स दिखाएँ';
$string['report:notes_title'] = 'विद्यार्थी नोट्स';
$string['report:nonotes'] = 'इस गतिविधि के लिए कोई नोट नहीं लिखा गया है।';
$string['report:notedate'] = 'लिखा गया';
$string['report:exportnotes_csv'] = 'नोट्स CSV के रूप में निर्यात करें';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'बंद करें';
$string['status:default'] = 'स्थिति अपडेट।';
$string['status:error'] = 'एक त्रुटि हुई। कृपया फिर से प्रयास करें।';
$string['rewindlabel'] = 'पीछे जाएँ';
$string['fastforwardlabel'] = 'तेज़ आगे';
$string['secondslabel'] = 'सेकंड';
$string['removenotelabel'] = 'नोट हटाएँ';
// ── Help strings ──
$string['gradepass_help'] = 'इस गतिविधि को पास करने के लिए आवश्यक न्यूनतम ग्रेड। इस या उससे अधिक ग्रेड पाने वाले विद्यार्थी पास माने जाते हैं।';


$string['completiondetail:requiredreactions'] = 'इन आवश्यक प्रतिक्रियाओं को शामिल करना होगा: {$a}';

$string['error:playbackrequired'] = 'यह कार्रवाई सहेजने से पहले वीडियो चल रहा होना चाहिए।';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'PHP GD एक्सटेंशन उपलब्ध नहीं है।';
$string['setting:gd_missing_desc'] = 'शिक्षकों द्वारा अपलोड की गई प्रतिक्रिया आइकन छवियाँ 64×64 पिक्सेल में स्वतः आकार नहीं बदलेंगी। मूल फ़ाइल वैसी ही दी जाएगी, जिससे बड़ी छवियों के लिए लोडिंग प्रदर्शन प्रभावित हो सकता है। स्वचालित आकार बदलना सक्षम करने के लिए सर्वर व्यवस्थापक से php-gd स्थापित करने को कहें।';

$string['report:heatmap_legend'] = 'प्रतिक्रिया हीटमैप रंग कुंजी';

$string['report:clusterlimitreached'] = 'रिपोर्ट में दिखाए जाने वाले क्लस्टरों की अधिकतम संख्या पहुँच गई है। पूर्ण विश्लेषण के लिए फ़िल्टर या छोटी समय-सीमा का उपयोग करें.';

$string['report:showingrecentreactionsoftotal'] = '{$a->total} प्रतिक्रियाओं में से {$a->shown} दिखाई जा रही हैं, सबसे पुरानी से सबसे नई तक।';

$string['report:viewfullreport'] = 'पूरी रिपोर्ट देखें';
$string['studentnotes_view_limited'] = 'नवीनतम {$a} नोट्स दिखाए जा रहे हैं। सभी नोट्स देखने के लिए पूरी रिपोर्ट खोलें।';
$string['report:skiptoheatmaptable'] = 'हीटमैप छोड़ें और डेटा तालिका पर जाएँ';
$string['report:heatmap_textsummary'] = 'चार्ट में {$a->clusters} क्लस्टर हैं; सबसे बड़े क्लस्टर में {$a->max} क्लिक हैं।';
$string['err:reactioniconvaluerequired'] = 'एक इमोजी या Font Awesome क्लास दर्ज करें.';
$string['err:reactioniconvalueinvalidfa'] = 'केवल मान्य Font Awesome class नाम दर्ज करें, जिनमें अक्षर, अंक, रिक्त स्थान और हाइफ़न हों।';

$string['error:reactionratelimit'] = 'कम समय में बहुत अधिक प्रतिक्रियाएँ भेजी गईं। कृपया वीडियो देखना जारी रखें और फिर प्रयास करें।';
$string['event:student_progress_reset'] = 'विद्यार्थी का VideoTrack डेटा रीसेट किया गया';
$string['report:timefrom'] = 'सेकंड से';
$string['report:timeto'] = 'सेकंड तक';
$string['report:clusterlimitreached_help'] = 'संचयी रिपोर्ट प्रदर्शित किए जा सकने वाले क्लस्टर की सीमा तक पहुँच गई है। आगे के क्लस्टर देखने के लिए उपयोगकर्ता, प्रतिक्रिया या वीडियो समय फ़िल्टर का उपयोग करें।';
$string['report:topclusterssummary'] = 'इस चयन में सबसे महत्वपूर्ण क्लस्टर:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} क्लिक';
$string['error:notesratelimit'] = 'कम समय में बहुत अधिक नोट भेजे गए हैं। अगला नोट जोड़ने से पहले प्रतीक्षा करें।';

$string['privacy:segmentschunk'] = 'वीडियो देखने के खंड - भाग {$a}';

$string['privacy:reactionsactivechunk'] = 'सक्रिय प्रतिक्रियाएँ - भाग {$a}';

$string['privacy:reactionsdeletedchunk'] = 'हटाई गई प्रतिक्रियाएँ - भाग {$a}';

$string['privacy:notesactivechunk'] = 'सक्रिय नोट - भाग {$a}';

$string['privacy:notesdeletedchunk'] = 'हटाए गए नोट - भाग {$a}';

$string['report:clusterlimitreached_csv'] = 'चेतावनी: क्लस्टर सीमा पहुँच गई है। निर्यात अधूरा हो सकता है; उपयोगकर्ता, प्रतिक्रिया या समय फ़िल्टर लगाकर फिर से निर्यात करें।';

$string['report:notecreatedfrom'] = 'इस तारीख से नोट';

$string['report:notecreatedto'] = 'इस तारीख तक नोट';

$string['reactionsavailableonlyduringplayback'] = 'प्रतिक्रियाएँ केवल वीडियो चलने के दौरान उपलब्ध हैं।';
$string['reactionsreadyannounce'] = 'प्रतिक्रियाएँ अब उपलब्ध हैं।';

$string['privacy:state'] = 'पूर्णता स्थिति';

$string['report:clusterlimitrequiresfilters'] = 'संचयी रिपोर्ट आंशिक है। शेष क्लस्टर विश्वसनीय रूप से प्राप्त करने के लिए वीडियो समय-सीमा फ़िल्टर लागू करें।';

$string['report:clusterlimitrequiresfilters_csv'] = 'संचयी निर्यात आंशिक है क्योंकि वीडियो समय-सीमा फ़िल्टर लागू नहीं किया गया। From second/To second फ़िल्टर लागू करें और फिर से निर्यात करें।';
$string['report:clusterexportblocked_csv'] = 'अधूरे डेटा से बचने के लिए निर्यात रोक दिया गया। वीडियो समय सीमा फ़िल्टर लागू करें और फिर से निर्यात करें।';
$string['report:clusterdisplayblocked'] = 'अधूरा डेटा दिखाने से बचने के लिए क्लस्टर तालिका छिपा दी गई है। जारी रखने के लिए वीडियो समय सीमा फ़िल्टर लागू करें।';
$string['unknownreaction'] = 'अज्ञात प्रतिक्रिया';

// Moodle HQ review fallback strings added in 1.0.29.
$string['externalprovider_notice'] = 'YouTube और Vimeo जैसे बाहरी वीडियो प्रदाता अपनी गोपनीयता नीतियों के अनुसार व्यक्तिगत डेटा संसाधित कर सकते हैं और कुकी सेट कर सकते हैं। जब तृतीय-पक्ष स्थानांतरण अनुमत न हो, तो अपलोड की गई फ़ाइलों का उपयोग करें।';
$string['privacy:metadata:youtube'] = 'जब YouTube वीडियो उपयोग किया जाता है, तो उपयोगकर्ता का ब्राउज़र वीडियो लोड और चलाने के लिए YouTube से जुड़ता है।';
$string['privacy:metadata:youtube:videoid'] = 'इस गतिविधि के लिए कॉन्फ़िगर किया गया YouTube वीडियो पहचानकर्ता।';
$string['privacy:metadata:youtube:url'] = 'इस गतिविधि के लिए कॉन्फ़िगर किया गया YouTube URL।';
$string['privacy:metadata:vimeo'] = 'जब Vimeo वीडियो उपयोग किया जाता है, तो उपयोगकर्ता का ब्राउज़र वीडियो लोड और चलाने के लिए Vimeo से जुड़ता है।';
$string['privacy:metadata:vimeo:videoid'] = 'इस गतिविधि के लिए कॉन्फ़िगर किया गया Vimeo वीडियो पहचानकर्ता।';
$string['privacy:metadata:vimeo:url'] = 'इस गतिविधि के लिए कॉन्फ़िगर किया गया Vimeo URL।';

$string['html5:controls'] = 'वीडियो नियंत्रण';
$string['html5:play'] = 'चलाएँ';
$string['html5:pause'] = 'विराम';
$string['html5:seek'] = 'ढूँढें';
$string['html5:volume'] = 'आवाज़';
$string['html5:mute'] = 'म्यूट करें';
$string['html5:unmute'] = 'म्यूट हटाएँ';
$string['html5:speed'] = 'गति';
$string['html5:pip'] = 'पिक्चर-इन-पिक्चर';
$string['html5:fullscreen'] = 'पूर्ण स्क्रीन';
$string['html5:download'] = 'डाउनलोड';
$string['setting:heading_privacy'] = 'गोपनीयता और डेटा संरक्षण';
$string['setting:heading_privacy_desc'] = 'कॉन्फ़िगर करें कि VideoTrack ट्रैकिंग, नोट और प्रतिक्रिया डेटा कैसे संग्रहीत करता है।';
$string['setting:retentionperioddays'] = 'ट्रैकिंग डेटा संरक्षण अवधि (दिन)';
$string['setting:retentionperioddays_desc'] = 'Retention cleanup के लिए पुराने tracking, note और reaction data (free-text reaction labels सहित) को anonymise करने से पहले दिनों की संख्या। 0 सेट करने पर data अनिश्चित समय तक रखा जाता है। Moodle Privacy API से आई user erasure requests चयनित context में user के tracking, state, reaction और note records को स्थायी रूप से delete करती हैं।';
$string['setting:retentionprivacynotice'] = 'Tracking data, notes और reactions व्यक्तिगत data हैं। वैध कानूनी आधार सुनिश्चित करें, site privacy notice अद्यतन रखें और उचित कारण न हो तो अनिश्चित retention से बचें।';
$string['setting:strictsessionvalidation'] = 'नोट और प्रतिक्रिया सत्यापन के लिए वही ब्राउज़र सत्र आवश्यक करें';
$string['setting:validationfallbackdays'] = 'ऐतिहासिक प्लेबैक सत्यापन विंडो (दिन)';
$string['setting:validationfallbackdays_privacywarning'] = 'अधिकतम सीमा के पास मान केवल दस्तावेजीकृत privacy और academic-integrity justification के साथ उपयोग करें।';
$string['setting:validationfallbackdays_desc'] = 'पहले देखे गए सेगमेंट की अधिकतम आयु, दिनों में, जो पेज रीफ्रेश या ब्राउज़र बदलने के बाद नोट्स और प्रतिक्रियाओं को अनुमति दे सकते हैं। 0 सेट करने पर ऐतिहासिक देखे गए सेगमेंट बिना समय सीमा के मान्य रहेंगे; यह उपयोगिता बढ़ाता है लेकिन शैक्षणिक अखंडता सत्यापन को अधिक उदार बनाता है। समान-सत्र और हालिया-प्लेबैक जांच हमेशा पहले की जाती हैं.';
$string['setting:strictsessionvalidation_desc'] = 'सक्षम होने पर, नोट और प्रतिक्रियाएँ केवल वर्तमान ब्राउज़र सत्र में देखे गए टाइमस्टैम्प के लिए सहेजी जा सकती हैं। अक्षम होने पर, VideoTrack उसी गतिविधि में उसी उपयोगकर्ता द्वारा पहले देखे गए टाइमस्टैम्प स्वीकार करता है, जिससे refresh या browser बदलने के बाद उपयोगिता बेहतर होती है और अनदेखी स्थितियाँ अस्वीकार रहती हैं।';
$string['task:cleanup'] = 'समाप्त VideoTrack ट्रैकिंग डेटा अनाम करें';
$string['privacy:anonymised'] = '[अनाम]';
$string['error:playbackpositionnotwatched'] = 'यह वीडियो स्थिति अभी तक नहीं देखी गई है, इसलिए कार्रवाई सहेजी नहीं जा सकती।';

$string['setting:intrangerequired'] = '{$a->min} और {$a->max} के बीच एक पूर्णांक दर्ज करें.';
$string['err:playerwidthrequired'] = 'प्लेटफ़ॉर्म डिफ़ॉल्ट का उपयोग करने के लिए 0 दर्ज करें, या 1 से 4096 पिक्सेल तक पूर्णांक दर्ज करें।';
$string['err:playbacksteprequired'] = '0 से 300 सेकंड तक की पूर्ण संख्या दर्ज करें। प्लेटफ़ॉर्म डिफ़ॉल्ट के लिए 0 उपयोग करें।';
$string['setting:nonnegativeintrequired'] = '0 या उससे अधिक पूर्ण संख्या दर्ज करें।';
$string['report:anonymiseduser'] = 'अनाम उपयोगकर्ता';
$string['report:exportnotes_privacywarning'] = 'इस निर्यात में विद्यार्थियों के नोट से व्यक्तिगत डेटा हो सकता है। इसे केवल वैध उद्देश्य होने पर डाउनलोड और संग्रहीत करें और आवश्यकता समाप्त होने पर हटा दें।';

$string['privacy:videoid_export_note'] = 'वीडियो/सामग्री पहचानकर्ता: {$a}';
$string['privacy:anonymisedreaction'] = 'अनामित प्रतिक्रिया';

// 1.3.87 accessibility and privacy confirmation strings.
$string['invalidvideosource'] = 'अमान्य वीडियो स्रोत।';
$string['report:gradeinputfor'] = '{$a} के लिए ग्रेड';
$string['report:savegradefor'] = '{$a} के लिए ग्रेड सहेजें';
$string['report:gradepassed'] = 'उत्तीर्ण';
$string['report:gradefailed'] = 'अनुत्तीर्ण';
$string['report:exportnotes_confirm'] = 'मैं पुष्टि करता/करती हूँ कि इस नोट्स निर्यात में व्यक्तिगत डेटा हो सकता है और इसे डाउनलोड करने का मेरा वैध उद्देश्य है।';
$string['report:exportnotes_confirmrequired'] = 'नोट्स डाउनलोड करने से पहले व्यक्तिगत डेटा निर्यात सूचना की पुष्टि करें।';
$string['coursereport:avgcoverage'] = 'औसत कवरेज: {$a}%';

$string['report:exportnotes_csv_personaldata'] = 'संभावित व्यक्तिगत डेटा सहित नोट्स को CSV के रूप में निर्यात करें';

$string['presets:deletearia'] = 'प्रीसेट {$a} हटाएँ';
$string['presets:reactionlabelaria'] = 'प्रतिक्रिया {$a}: लेबल';
$string['presets:reactiondescriptionaria'] = 'प्रतिक्रिया {$a}: विवरण';
$string['presets:reactionicontypearia'] = 'प्रतिक्रिया {$a}: आइकन प्रकार';
$string['presets:reactioniconvaluearia'] = 'प्रतिक्रिया {$a}: आइकन मान';
$string['presets:reactionrequiredaria'] = 'प्रतिक्रिया {$a}: पूर्णता के लिए आवश्यक';
$string['err:reactionpresetjson'] = 'प्रतिक्रिया प्रीसेट डेटा अमान्य है। पृष्ठ पुनः लोड करें और फिर प्रयास करें।';
$string['presets:reactionstablecaption'] = 'प्रतिक्रिया प्रीसेट पंक्तियाँ';
$string['privacy:intervals_none'] = 'कोई देखने का अंतराल दर्ज नहीं किया गया।';
$string['privacy:intervals_unavailable'] = 'देखने के अंतराल उपलब्ध नहीं हैं या अमान्य हैं।';

$string['warning:suspicioussegment'] = 'देखने का खंड दर्ज नहीं किया गया क्योंकि यह अपेक्षित प्लेबैक विंडो से अधिक था। सामान्य रूप से देखना जारी रखें और फिर पुनः प्रयास करें।';

$string['event:notes_exported'] = 'व्यक्तिगत नोट निर्यात किए गए';

$string['externalproviderprivacy_notice'] = 'यह गतिविधि {$a} से वीडियो लोड करती है। आपका ब्राउज़र साइट की गोपनीयता सूचना के अनुसार इस प्रदाता को IP पता, user agent और cookies जैसे तकनीकी डेटा भेज सकता है।';

$string['setting:retentionunlimitedwarning_title'] = 'VideoTrack की unlimited retention सक्षम है।';

$string['setting:retentionunlimitedwarning_desc'] = '0 मान tracking data, notes और reactions को अनिश्चित समय तक रखता है। पुष्टि करें कि यह आपकी GDPR/privacy policy के अंतर्गत उचित है, या 730 दिनों जैसी finite retention अवधि सेट करें।';

$string['warning:notetruncated'] = 'नोट सहेज दिया गया, लेकिन site द्वारा अनुमत अधिकतम length तक छोटा कर दिया गया।';

$string['error:securetokenunavailable'] = 'एक सुरक्षित रैंडम टोकन जनरेटर उपलब्ध नहीं है। VideoTrack अज्ञातीकरण कुंजियाँ सुरक्षित रूप से नहीं बना सकता।';

$string['hiddeninstancelabel'] = 'विद्यार्थियों से छिपा हुआ: {$a}';

$string['setting:nonnegativeintmax'] = 'मान {$a} से अधिक नहीं होना चाहिए।';

$string['restore_missing_reaction_mapping'] = 'mod_videotrack restore: purane reaction id {$a} ke liye reaction mapping nahi mili; ek hidden placeholder reaction banayi ja rahi hai.';
$string['restore_placeholder_reaction'] = 'पुनर्स्थापित प्रतिक्रिया';
$string['privacy_cleanup_failed'] = 'VideoTrack GDPR रिटेंशन सफाई विफल रही: {$a}';
$string['privacy_cleanup_unlimited'] = 'VideoTrack GDPR रिटेंशन: असीमित रिटेंशन कॉन्फ़िगर है; कोई रिकॉर्ड अनामित नहीं किया गया।';
$string['privacy_cleanup_anonymised'] = 'VideoTrack GDPR रिटेंशन: {$a->processed} उपयोगकर्ता/गतिविधि जोड़ों में {$a->segments} सेगमेंट, {$a->states} स्थिति रिकॉर्ड और {$a->events} प्रतिक्रिया/नोट ईवेंट अनामित किए गए।';
$string['privacy_cleanup_remaining'] = 'अधिक रिकॉर्ड शेष हैं और उन्हें बाद के रन में संसाधित किया जाएगा: {$a}।';
