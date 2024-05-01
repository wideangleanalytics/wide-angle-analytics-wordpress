=== Wide Angle Analytics ===
Contributors: jrozanski,inputobjects
Tags: web analytics, tracking, web traffic, analytics, statistics, stats
Requires at least: 5.2
Tested up to: 6.5.2
Requires PHP: 7.2
Stable tag: 1.0.8
License: GPLv2

Easily add Wide Angle Analytics tracker script to your WordPress site. You can quickly configure your web analytics tracker script.

== Description ==
[Wide Angle Analytics](https://wideangle.co) is a privacy-friendly, GDPR-compliant web analytics service. You can deploy it cookieless or with cookies enabled.

We promote remote configuration, meaning you configure the tracker behaviour directly from our service interface. However, to give you full control, some settings must be applied before initial communication with our service takes place.

We provide you with the ability to configure the tracker script with few settings, making sure that you are tracking your traffic effectively. At the same time, we give you tools to prevent leaking any or unintended Personal Data.

Our plugin offers you as easy to use configuration wizard. The plugin will automatically inject the necessary HTML to both Header and Footer to make the script record website visits.

Once configured, you will get insight into your web traffic. You will get information about visitor:

1. count (unique and total page views),
2. location, down to province level,
3. browser and operating system,
4. pages viewed, and
5. where these visitors came from.

We are actively adding more data points and enrich the dashboard with additional information.

== Screenshots ==
1. Configuration Wizard in Settings
2. Your Site Setting in Wide Angle Analytics
3. Site Traffic data collected in Wide Angle Analytics Dashboard

== Frequently Asked Questions ==

= Do I need Wide Angle Analytics account? =

Yes. To start using tracking code, you will need a valid Site ID. The Site ID is available in Wide Angle Analytics Site Settings panel.

= Do I need Cookie Banner =
Default configuration of  Wide Angle Analytics does not collect any Personal Data and uses no Browser Cookies. Assuming you don't have any other trackers on your site, you won't need a Cookie Banner.

Wide Angle Analytics does support Cookie-enable mode. We give you fine-grained control over how you collect information about your traffic. We believe you know best.

[Full list of data we collect](https://wideangle.co/documentation/data-and-privacy)

= Why isn't Wide Angle Analytics Free =

Some Web Analytics platforms are free, at least some amount of traffic. Google Analytics is a very prominent example. It is free up to 10 million events.

It is free because data about your traffic and your visitors is feeding Google's Advertising business. By getting the service for free, you are indirectly supporting your competition in having a more efficient advertising platform. You are also exposing your visitors to aggressive monitoring and tracking.

Wide Angle Analytics is not free. For a fee, you get service that handles your data with utmost respect. No sharing. No reselling. This is your data used solely for your analytics and business purposes.

We run a business around serving the needs of our customer. That's you.

= Where can I find detailed documentation about Wide Angle Analytics? =

You will find most of your answers in the [Knowledge Base](https://wideangle.co/documentation). Should you require further assistance, please [get in touch](https://wideangle.co/support) with our team.

== Changelog ==
V1.0.8
- Expose DNT Flag override
- Test with WordPress 6.5.2
- Drop un-used ePrivacy flag

V1.0.7
- Fix configuration of custom RegEx in the exclusion paths
- Test with WordPress 6.1.1

V1.0.6
- Support for ePrivacy Mode configuration.

V1.0.5
- Support for `data-waa-fingerprint` toggle enabling optional browser fingerprinting.
- Fix the header generate to use valid `prefetch` attribute
