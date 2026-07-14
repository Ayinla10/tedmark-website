-- ============================================================
-- Replace the Services page lineup with the new AI-focused
-- service offering (per user's exact list, 2026-07-14).
-- Run ONCE in phpMyAdmin on database: hopewwkz_tedmark
-- ============================================================

DELETE FROM `services`;
ALTER TABLE `services` AUTO_INCREMENT = 1;

INSERT INTO `services` (`title`, `slug`, `icon`, `color`, `description`, `features`, `sort_order`) VALUES
('AI Agent Development', 'ai-agent-development', 'fa-solid fa-robot', '#22c55e', 'Build intelligent AI agents for customer support, internal operations, documents, voice, chat, and workflow automation.', '', 1),
('AI Operating System', 'ai-operating-system', 'fa-solid fa-server', '#a78bfa', 'Deploy, manage, monitor, and govern every AI agent, workflow, and business knowledge base from one central platform.', '', 2),
('AI Marketing', 'ai-marketing', 'fa-solid fa-bullhorn', '#f43f5e', 'Automate lead generation, CRM, outreach, content, and customer engagement with AI-powered marketing systems.', '', 3),
('AI Strategy & Consulting', 'ai-strategy-consulting', 'fa-solid fa-compass', '#f59e0b', 'Identify high-impact AI opportunities, define implementation roadmaps, and guide your organization from idea to deployment.', '', 4),
('AI Adoption & Training', 'ai-adoption-training', 'fa-solid fa-graduation-cap', '#14b8a6', 'Equip your teams with practical AI skills, playbooks, and workflows that drive real adoption and measurable productivity.', '', 5),
('Web/App Development', 'web-app-development', 'fa-solid fa-code', '#60a5fa', 'Design and build AI-powered web and mobile applications using modern technologies like React, Next.js, React Native, etc.', '', 6);
